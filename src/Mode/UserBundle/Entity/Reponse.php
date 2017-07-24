<?php
/**
 * Created by PhpStorm.
 * User: mor
 * Date: 19/07/17
 * Time: 23:58
 */

namespace Mode\UserBundle\Entity;


class Reponse
{
    protected $code;
    protected $message;

    /**
     * Response constructor.
     * @param $code
     * @param $message
     */
    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }



}