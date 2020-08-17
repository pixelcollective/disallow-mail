<?php

/*
 * Plugin Name:  Disallow Mail
 * Plugin URI:   https://tinypixel.dev
 * Description:  Disallow mailings on non-production environments.
 * Version:      1.0.0
 * Author:       Tiny Pixel
 * Author URI:   https://tinypixel.dev
 * License:      MIT License
 */

(new class {
    /**
     * Class invocation.
     */
    public function __invoke()
    {
        if (!$this->mailDisabled()) return;

        $this->runHooks();
    }

    /**
     * Run WP hooks.
     *
     * @return void
     */
    public function runHooks(): void
    {
        add_filter('gettext', [$this, 'hidePasswordReset']);
        add_filter('allow_password_reset', [$this, 'disablePasswordReset']);
        add_filter('wp_mail', [$this, 'unsetRecipients'], 10, 1);
        add_action('phpmailer_init', [$this, 'disablePHPMailer']);
    }

    /**
     * Mail disabled check.
     *
     * @return bool true if mail is disabled
     */
    public function mailDisabled(): bool
    {
        return defined('DISABLE_MAIL') && DISABLE_MAIL;
    }

    /**
     * Filter wp_mail recipients.
     *
     * @param  array wp mail arguments
     * @return array wp mail arguments
     */
    public function unsetRecipients($args): array
    {
        unset($args['to']);

        return $args;
    }

    /**
     * Overwrite phpmailer instance to prevent message sends.
     *
     * @param  \PHPMailer\PHPMailer\PHPMailer  wp phpmailer instance
     * @return void
     */
    public function disablePHPMailer(\PHPMailer\PHPMailer\PHPMailer $phpmailer): void
    {
        global $phpmailer;

        $phpmailer = new \PHPMailer\PHPMailer\PHPMailer (true);
    }

    /**
     * Disable lost password functionality.
     *
     * @return bool return false to disallow password reset
     */
    public function disablePasswordReset(): bool
    {
        return false;
    }

    /**
     * Hide lost password from wp-login.
     *
     * @param  string text
     * @return string text
     */
    public function hidePasswordReset(string $text): string
    {
        return $text == 'Lost your password?' ? '' : $text;
    }
})();
