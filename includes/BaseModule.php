<?php
/**
 * BaseModule Class
 *
 * @author Renfei Song
 * @since 2.0.0
 */

/**
 * Class BaseModule
 *
 * The BaseModule class is the base class that you subclass in order to implement certain services. A service takes
 * user input that is passed to it, does some queries according to the input data, and returns the result message to
 * user.
 * For example, a phone book service might take a valid name in the database and returns the phone number associated
 * to that name.
 * Also see the {@link https://github.com/renfeisong/buaasoft-wechat/wiki/Module-Programming-Guide}
 * for more information and examples on how to create a module.
 */
class BaseModule {

    /**
     * Prepares your module.
     *
     * The default implementation of this method does nothing. This method is called right after the module is loaded,
     * which is prior to receiving user messages. If your service requires background execution, you can use this
     * method to register action hooks.
     */
    public function prepare() {
        ;
    }

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
     * Note that this priority may be overwritten by global settings.
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

    /**
     * Returns the display name of your module.
     *
     * This method returns the class name by default. Subclasses may override this method and returns a more
     * human-readable name if the module has a settings page.
     *
     * @return string
     */
    public function display_name() {
        return get_class($this);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    //// You should not override or call the methods below.
    //// For internal use only.

    public function has_settings_page() {
        return file_exists(ABSPATH . 'modules/' . get_class($this) . '/settings.php');
    }

    public function settings_page_url() {
        return ROOT_URL . 'modules/' . get_class($this) . '/settings.php';
    }
}