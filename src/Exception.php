<?php

namespace Openbuildings\Postmark;

use Swift_TransportException;

/**
 * Postmark API exception like sending email to inactive emails
 * Extends SwiftTransport Exception to be caught by SwiftMailer exception handling
 */
class Exception extends Swift_TransportException
{
    const BAD_OR_MISSING_API_TOKEN                                  =  10;
    const INVALID_EMAIL_REQUEST                                     = 300;
    const SENDER_SIGNATURE_NOT_FOUND                                = 400;
    const SENDER_SIGNATURE_NOT_CONFIRMED                            = 401;
    const INVALID_JSON                                              = 402;
    const INCOMPATIBLE_JSON                                         = 403;
    const NOT_ALLOWED_TO_SEND                                       = 405;
    const INACTIVE_RECIPIENT                                        = 406;
    const JSON_REQUIRED                                             = 409;
    const TOO_MANY_BATCH_MESSAGES                                   = 410;
    const FORBIDDEN_ATTACHEMENT_TYPE                                = 411;
    const SENDER_SIGNATURE_QUERY_EXCEPTION                          = 500;
    const SENDER_SIGNATURE_NOT_FOUND_BY_ID                          = 501;
    const NO_UPDATED_SENDER_SIGNATURE_DATA_RECEIVED                 = 502;
    const YOU_CANNOT_USE_A_PUBLIC_DOMAIN                            = 503;
    const SENDER_SIGNATURE_ALREADY_EXISTS                           = 504;
    const DKIM_ALREADY_SCHEDULED_FOR_RENEWAL                        = 505;
    const THIS_SENDER_SIGNATURE_ALREADY_CONFIRMED                   = 506;
    const YOU_DO_NOT_OWN_THIS_SENDER_SIGNATURE                      = 507;
    const SERVER_QUERY_EXCEPTION                                    = 600;
    const SERVER_DOES_NOT_EXIST                                     = 601;
    const DUPLICATE_INBOUND_DOMAIN                                  = 602;
    const SERVER_NAME_ALREADY_EXISTS                                = 603;
    const YOU_DONT_HAVE_DELETE_ACCESS                               = 604;
    const UNABLE_TO_DELETE_SERVER                                   = 605;
    const INVALID_WEBHOOK_URL                                       = 606;
    const INVALID_SERVER_COLOR                                      = 607;
    const SERVER_NAME_MISSING_OR_INVALID                            = 608;
    const NO_UPDATED_SERVER_DATA_RECEIVED                           = 609;
    const INVALID_MX_RECORD_FOR_INBOUND_DOMAIN                      = 610;
    const MESSAGES_QUERY_EXCEPTION                                  = 700;
    const MESSAGE_DOESNT_EXIST                                      = 701;
    const TRIGGER_QUERY_EXCEPTION                                   = 800;
    const TRIGGER_FOR_THIS_TAG_DOESNT_EXIST                         = 801;
    const TAG_WITH_THIS_NAME_ALREADY_HAS_TRIGGER_ASSOCIATED_WITH_IT = 803;
    const NAME_TO_MATCH_IS_MISSING                                  = 808;
    const NO_TRIGGER_DATA_RECEIVED                                  = 809;

    /**
     * Create a new Exception with $message and $code
     *
     * @param string $message
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        // In SwiftMailer <5.4.1
        // Swift_SwiftException does not conform to built-in Exception signature
        $this->code = $code;
    }
}
