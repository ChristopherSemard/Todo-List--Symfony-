<?php

namespace App\Security;

use App\Entity\User; // your user entity
use App\Security\Exception\NotVerifiedEmailException;
use App\Security\Exception\EmailAlreadyUsedException;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Security;


class GithubAuthenticator extends OAuth2Authenticator
{
    private $clientRegistry;
    private $entityManager;
    private $router;
    private $client;

    public function __construct(HttpClientInterface $client, ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->client = $client;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'app_github/connect/check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('github');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var githubUser $githubUser */
                $githubUser = $client->fetchUserFromToken($accessToken);
                // dd($accessToken->getToken());
                // Récupérer l'email pour GITHUB
                $response = $this->client->request(
                    'GET',
                    'https://api.github.com/user/emails',
                    [
                        'headers' => [
                            'Accept' => 'application/vnd.github.v3+json',
                            'Authorization' => "token {$accessToken->getToken()}",
                        ]
                    ]
                );
                $emails = json_decode($response->getContent(), true);
                foreach ($emails as $key => $email) {
                    if ($email['primary'] == true && $email['verified'] == true) {
                        $data = $githubUser->toArray();
                        $data['email'] = $email['email'];
                        $githubUser = new GithubResourceOwner($data);
                    }
                }
                if ($githubUser->getEmail() == null) {
                    throw new NotVerifiedEmailException();
                }

                $arrayGithubUser = $githubUser->toArray();
                $email = $arrayGithubUser['email'];

                // 1) have they logged in with github before? Easy!
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['githubId' => $githubUser->getId()]);
                if ($existingUser) {
                    return $existingUser;
                }

                // 2) do we have a matching user by email?
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($user) {
                    throw new EmailAlreadyUsedException();
                } else {
                    $user = new User();
                    // 3) Maybe you just want to "register" them by creating
                    // a User object
                    $user->setEmail($arrayGithubUser['email']);
                    $user->setUsername($arrayGithubUser['login']);
                    $user->setAvatar($arrayGithubUser['avatar_url']);
                    $user->setGithubId($arrayGithubUser['id']);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('app_home');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }


        $targetUrl = $this->router->generate('app_login');

        return new RedirectResponse($targetUrl);
    }
}
