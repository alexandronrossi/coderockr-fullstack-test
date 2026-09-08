<?php

namespace App\Support\Auth;

final class AuthFieldLimits
{
    public const NAME_MAX = 255;

    public const EMAIL_MAX = 255;

    /** Bcrypt truncates beyond 72 bytes; reject earlier for clear validation errors. */
    public const PASSWORD_MAX = 72;

    public const PASSWORD_MIN = 8;
}
