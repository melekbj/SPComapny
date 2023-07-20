<?php

namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $userProvider;

    public function __construct(private UrlGeneratorInterface $urlGenerator, UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }


    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        $user = $this->userProvider->loadUserByIdentifier($email);

        if ($user) {
            if ($user->getRoles() == 'ROLE_SUPER_USER' && $user->getEtat() == 'pending') {
                throw new CustomUserMessageAuthenticationException('Account pending approval. Please check your email for further instructions.');
            }
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        $user = $token->getUser();
        
        return new RedirectResponse($this->urlGenerator->generate('app_admin'));
        // return new RedirectResponse($this->urlGenerator->generate('some_route'));
        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}



// public function authenticate(Request $request): Passport
//     {
//         $email = $request->request->get('email', '');

//         $request->getSession()->set(Security::LAST_USERNAME, $email);

//         $user = $this->userProvider->loadUserByIdentifier($email);

//         if ($user) {
//             if ($user->getRoles() == 'ROLE_SUPER_USER' && $user->getEtat() == 'pending') {
//                 throw new CustomUserMessageAuthenticationException('Account pending approval. Please check your email for further instructions.');
//             }
//         }

//         return new Passport(
//             new UserBadge($email),
//             new PasswordCredentials($request->request->get('password', '')),
//             [
//                 new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
//             ]
//         );
//     }