<?php
if (!defined('ABSPATH')) exit;
uidebug($product_link);
?>

<h2><?php echo esc_html($email_heading); ?></h2>
<p><strong>Product Name:</strong> <?php echo esc_html($product_title); ?></p>
<p><a href="<?php echo esc_url($product_link); ?>">View Product</a></p>
