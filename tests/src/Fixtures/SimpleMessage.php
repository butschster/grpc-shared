<?php

namespace Tests\Fixtures;

use Google\Protobuf\Internal\Message;

/**
 * &#64;Guarded
 */
class SimpleMessage extends Message
{
    /**
     * &#64;DefaultValue(value=false)
     * &#64;Type("Shared\\ValueObjects\\Email")
     *
     * Generated from protobuf field <code>string email = 1;</code>
     */
    protected $email = '';

    /**
     * &#64;Optional
     * &#64;Type("Shared\\ValueObjects\\Password")
     *
     * Generated from protobuf field <code>string company_name = 3;</code>
     */
    protected $password = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     * @type string $email
     * @type string $password
     * }
     */
    public function __construct($data = null)
    {
    }
}

