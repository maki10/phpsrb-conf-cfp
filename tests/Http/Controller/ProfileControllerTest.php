<?php

namespace OpenCFP\Test\Http\Controller;

use Mockery as m;
use OpenCFP\Domain\Model\User;
use OpenCFP\Test\DatabaseTransaction;
use OpenCFP\Test\WebTestCase;
use Spot\Locator;

/**
 * Class ProfileControllerTest
 *
 * @package OpenCFP\Test\Http\Controller
 * @group db
 */
class ProfileControllerTest extends WebTestCase
{
    use DatabaseTransaction;

    public function setUp()
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->tearDownDatabase();
    }

    /**
     * @test
     */
    public function notAbleToSeeEditPageOfOtherPersonsProfile()
    {
        $this->asLoggedInSpeaker(1)
            ->get('/profile/edit/2')
            ->assertNotSee('My Profile')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function seeEditPageWhenAllowed()
    {
        $spot = m::mock(Locator::class);
        $spot->shouldReceive('mapper')->with('\OpenCFP\Domain\Entity\User')->andReturn($spot);
        $spot->shouldReceive('get->toArray')->with()->andReturn(
            [
                'first_name' => 'Speaker Name',
                'last_name' => 'Last Name',
                'company' => '',
                'twitter' => '',
                'info' => 'My information',
                'bio' => 'Interesting details about my life',
                'photo_path' => '',
                'url' => '',
                'airport' => '',
                'transportation' => '',
                'hotel' => '',
            ]
        );

        $this->swap('spot', $spot);
        $this->asLoggedInSpeaker()
            ->get('/profile/edit/1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function notAbleToEditOtherPersonsProfile()
    {
        $this->asLoggedInSpeaker(1)
            ->post('/profile/edit', ['id' =>2])
            ->assertNotSee('My Profile')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function canNotUpdateProfileWithInvalidData()
    {
        $this->asLoggedInSpeaker()
            ->post('/profile/edit', $this->putUserInRequest(false))
            ->assertSee('My Profile')
            ->assertSee('Invalid email address format')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function redirectToDashboardOnSuccessfulUpdate()
    {
        $user = factory(User::class, 1)->create()->first();
        $this->asLoggedInSpeaker($user->id)
            ->post('/profile/edit', $this->putUserInRequest(true, $user->id))
            ->assertNotSee('My Profile')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function displayChangePasswordWhenAllowed()
    {
        $this->asLoggedInSpeaker()
            ->get('/profile/change_password')
            ->assertSee('Change Your Password')
            ->assertSuccessful();
    }

    /**
     * Helper function to fake a user in the request object.
     *
     * @param $isEmailValid bool whether or not to use a valid email address
     *
     * @return array
     */
    private function putUserInRequest($isEmailValid, $id = 1): array
    {
        return [
            'id' => $id,
            'email' => $isEmailValid ? 'valideamial@cfp.org' : 'invalidEmail',
            'first_name' => 'First',
            'last_name' => 'Last',
        ];
    }
}
