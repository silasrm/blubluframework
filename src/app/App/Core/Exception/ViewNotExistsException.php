<?php

/**
 * This class represent an exception when view file does not exists.
 * @package App
 * @subpackage App\Core\Exception
 */
namespace App\Core\Exception;

class ViewNotExistsException extends \Exception
{
	protected $message = 'View file not exists.';
}