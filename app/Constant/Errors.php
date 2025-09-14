<?php

class Errors
{

    const ErrorCode = [
        400 => "Bad Request",
        401 => "Unauthorized",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        409 => "Conflict",
        1002 => "User not found in DB.",
        1003 => " User email verification token not found in DB.",
        1004 => "User forgot password token not found in DB.",
        1005 => "User current password is invalid",
        1006 => "User request reservation duration exceeds max allowed",
        2000 => "DropOff Black Out",
        2001 => "PickUp Black Out",
        2002 => "STAY Black Out",
        2003 => "MIN STAY Black Out",
        3001 => "Reservation is already paid",
        4001 => "User referred already exists",
        4002 => "UserName was already referred",
        6 => "Reservation cannot be cancelled after checked out",
        7 => "Reservation cannot be cancelled after checked in",
        8 => "Reservation is already cancelled",
        9 => "Reservation has no pricing which is unpaid",
        10 => "The operation is invalid",
    ];

}