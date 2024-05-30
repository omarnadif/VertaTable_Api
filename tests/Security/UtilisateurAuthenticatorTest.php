<?php 
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Security\UtilisateurAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UtilisateurAuthenticatorTest extends KernelTestCase
{
    public function testAuthenticate()
{
    self::bootKernel();
    $container = static::getContainer();  // Modifica qui
    $authenticator = $container->get(UtilisateurAuthenticator::class);
    $request = Request::create('/api/login', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['email' => 'test@example.com', 'password' => 'password']));

    $userProvider = $this->createMock(UserProviderInterface::class);
    $passport = $authenticator->authenticate($request);

    $this->assertNotEmpty($passport);
    // Add more assertions here to check what's expected
}

}

