<?php

namespace App\Enums;

enum VerificationTypeEnum: string
{
    case LOGIN = 'login';
    case REGISTRATION = 'registration';
    case PHONE_CHANGE = 'phone_change';
    case EMAIL_CHANGE = 'email_change';

    case DELETE_ACCOUNT = 'delete_account';
}
