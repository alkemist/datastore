<?php

namespace App\Model;

use App\Trait\TokenTrait;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;

class UserToken extends OAuthUser
{
    use TokenTrait;
}