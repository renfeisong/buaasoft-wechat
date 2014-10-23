<?php
/**
 * The logout utility of Admin Panel.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once dirname(__FILE__) . '/includes/admin.php';

log_out();

redirect('index.php');