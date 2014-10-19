<?php
/**
 * BaseModule Class
 *
 * @author Renfei Song
 * @since 1.0
 */

require_once "UserInput.php";

/**
 * Class BaseModule
 *
 * The BaseModule class is the base class that you subclass in order to implement certain services. A service takes
 * user input that is passed to it, does some queries according to the input data, and returns the result message to
 * user.
 * For example, a phone book service might take a valid name in the database and returns the phone number associated
 * to that name.
 */
class BaseModule {

    /**
     * Returns a boolean indicating whether the service can act on the input data.
     *
     * The default implementation of this method returns false. Subclasses must override it and return true if the
     * input data in the `input` parameter can be operated by your service. Your implementation should check the
     * contents of the object and determine if your service can act on the corresponding data.
     *
     * The engine internally calls this method when determining which service to apply to user's message.
     *
     * @param UserInput $input An object representing user's input.
     * @return bool true if your service can act on the specified input data or false if it cannot.
     */
    public function can_handle_input(UserInput $input) {
        return false;
    }

    /**
     * Returns an integer that identifies the priority of this service.
     *
     * This method returns 10 by default. Subclasses may override this method and return a valid integer. The integer
     * is used to determine which service to apply when multiple services can act on the same user input data.
     *
     * @return int The priority of this service.
     */
    public function priority() {
        return 10;
    }


    /**
     * Returns a standard-compliant string as the output of your service.
     *
     * This method returns empty string by default. Subclasses must override this method and returns a valid XML string
     * as per the API documentation by WeChat.
     *
     * See the {@link http://mp.weixin.qq.com/wiki/index.php?title=发送被动响应消息} for more information.
     *
     * It's recommended to use OutputFormatter to construct the output string.
     *
     * @param UserInput $input An object representing user's input.
     * @return string The output string (in XML format).
     */
    public function raw_output(UserInput $input) {
        return "";
    }

} 