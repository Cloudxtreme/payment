<form method="POST">
    <fieldset>

        <legend>Your information</legend>

        <ol>

            <li>
                <label for="firstName"><?php echo __('First name'); ?></label>
                <input type="text" id="firstName" name="firstName" />
            </li>

            <li>
                <label for="name"><?php echo __('Last name'); ?></label>
                <input type="text" id="name" name="name" />
            </li>

            <li>
                <label for="email"><?php echo __('Email'); ?></label>
                <input type="text" id="email" name="email" />
            </li>

            <li>
                <label for="password1"><?php echo __('Password'); ?></label>
                <input type="text" id="password1" name="password1" />
            </li>

            <li>
                <label for="password2"><?php echo __('Confirm password'); ?></label>
                <input type="text" id="password2" name="password2" />
            </li>

        </ol>
    </fieldset>

    <fieldset>

        <legend>Your account</legend>

        <ol>
            <li>
                <label for="accountname"><?php echo __('Account name'); ?></label>
                <input type="text" id="accountname" name="accountname" />
            </li>
        </ol>

    </fieldset>

    <fieldset>

        <legend>Select a plan</legend>

        <ol>

            <?php foreach ($plans as $v) { ?>

                <!--
                    <?php print_r ($v); ?>
                -->

                <li>
                    <input type="radio" id="plan<?php echo $v['id']; ?>" name="plan" value="1" />
                    <label for="plan<?php echo $v['id']; ?>">
                        <?php echo $v['name']; ?>

                        <!-- Limits, feel free to remove. -->
                        <?php
                            $out = "(";
                            foreach ($v['limits'] as $k => $v) {
                                $out .= $v . " " . $k . ", ";
                            }
                            $out = substr ($v, 0, -2) . ".)";

                            echo $out;
                        ?>
                    </label>
                </li>
            <?php } ?>
        </ol>

    </fieldset>

    <fieldset>
        <ol>
            <li>
                <button type="submit">Register</button>
            </li>
        </ol>
    </fieldset>
</form>