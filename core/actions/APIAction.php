<?php

namespace AdSky\Core\Actions;

/**
 * Represents an abstract API Action.
 */

abstract class APIAction {

    /**
     * Executes the action.
     *
     * @return Response An API Response.
     */

    abstract function execute();

}