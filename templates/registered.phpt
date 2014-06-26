<div class="done">
    <p><?php echo __('Thanks! Your account is pending.'); ?></p>
    <p><?php echo __('Please follow the instrucitons on PayPal to setup the payment plan.'); ?></p>
    <p><?php echo __('When that\'s done, we\'ll finish creating your account!'); ?></p>
</div>

<form name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">
    <input type="hidden" name="cmd" value="_xclick-subscriptions">
    <input type="hidden" name="business" value="paypal@catlab.be">
    <input type="hidden" name="currency_code" value="EUR">
    <input type="hidden" name="no_shipping" value="1">
    <input type="image" src="http://www.paypal.com/en_US/i/btn/btn_subscribe_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
    <input type="hidden" name="a3" value="<?php echo $price; ?>">
    <input type="hidden" name="p3" value="1">
    <input type="hidden" name="t3" value="Y">
    <input type="hidden" name="src" value="1">
    <input type="hidden" name="sra" value="1">

    <input type="hidden" name="notify_url" value="<?=$notify_url?>">
    <input type="hidden" name="item_name" value="<?php echo $name; ?>">
    <input type="hidden" name="item_number" value="<?php echo $number; ?>">
</form>