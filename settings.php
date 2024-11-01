<style type="text/css">

    input.streamin_user_key { width: 100%; }
    input.streamin_user_key:disabled { background-color: transparent; border: none; }
    .streamin_user_key_overlay { cursor: pointer }

</style>

<form method="post" action="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/index.php">
    <input type="hidden" name="streamin_action" value="update_settings" />

    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br></div>
        <h2>
            StreamIn Webhooks
            <span style="float:right; font-size: 14px">
                <a target="_blank" href="http://streamin.io">StreamIn</a>
            </span>
        </h2>

        <p>List of available webhooks:</p>

        <table id="streamin_webhooks_table" class="widefat" cellspacing="0">
            <thead>
            <tr>
                <th scope="coll" class="manage-column" style="width:5   %">Enabled</th>
                <th scope="coll" class="manage-column" style="width:15%">Webhook</th>
                <th scope="coll" class="manage-column" style="width:25%">User Key<span style="color: #b80000; font-size: 16px">*</span></th>
                <th scope="coll" class="manage-column">Description</th>
                <th scope="coll" class="manage-column">Example</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($STREAMIN_WEBHOOKS as $webhook => $info) { ?>
            <tr>
                <td>
                    <input type="checkbox"
                           name="<?php echo $webhook; ?>_check" <?php echo isset($webhooks[$webhook]) ? 'checked="true"' : ""; ?> />
                </td>
                <td>
                    <b><?php echo $info['title']; ?></b>
                </td>
                <td style="position: relative">
                    <div title="Double click to edit" class="streamin_user_key_overlay"
                         style="position:absolute; left:0; right:0; top:0; bottom:0;"></div>
                    <input class="streamin_user_key" maxlength="32" disabled="true" name="<?php echo $webhook; ?>_key"
                           type="text" value="<?php echo isset($webhooks[$webhook]) ? $webhooks[$webhook] : ''; ?>"/>
                </td>
                <td><?php echo $info['description']; ?></td>
                <td><?php echo $info['example']; ?></td>
            </tr>
                <?php } ?>
            </tbody>
            <thead>
            <tr>
                <th scope="coll" class="manage-column">Enabled</th>
                <th scope="coll" class="manage-column">Webhook</th>
                <th scope="coll" class="manage-column">User Key<span style="color: #b80000; font-size: 16px">*</span></th>
                <th scope="coll" class="manage-column">Description</th>
                <th scope="coll" class="manage-column">Example</th>
            </tr>
            </thead>
        </table>

        <p>
            <span style="color: #b80000; font-size: 16px">*</span>
            You can obtain your user key when you subscribe to a WordPress notification source in your
            <a target="_blank" href="http://app.streamin.io/office">Streamin web office</a>.
            Double click on user key fields to edit them.
        </p>

        <?php submit_button(); ?>
    </div>
</form>

<h3>Webhook not listed?</h3>
<p>You need a webhook for a WordPress action or event that is not listed? Contact us at <a herf="mailto:office@streamin.io">office@streamin.io</a></p>

<h3>Missed a notification?</h3>
<p>You enabled a webhook but received no notification via StreamIn? Check StreamIn log <a href="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=streamin-logs">here</a>.</p>

<script type="text/javascript">

    // On Load
    jQuery(function(){
        // set checkbox behaviour
        jQuery('input[type="checkbox"]').change(function(){
            var input = jQuery(this).parent().parent().find('input.streamin_user_key');
            if(jQuery(this).is(':checked')) input.removeAttr('disabled').focus().prev('.streamin_user_key_overlay').hide();
            else input.attr('disabled','true').prev('.streamin_user_key_overlay').show();
        });

        // set user key overlay behaviour
        jQuery('.streamin_user_key_overlay').dblclick(function(){
            jQuery(this).hide().next('input.streamin_user_key').removeAttr('disabled').focus();
        });

        // set user key input behaviour
        jQuery("input.streamin_user_key").keyup(function(e){
            if(e.which != 13) return;
            jQuery(this).attr('disabled','true').prev('.streamin_user_key_overlay').show();
            e.preventDefault();
            return false;
        });

        // set user key input behaviour to stop form submit
        jQuery("input.streamin_user_key").keypress(function(e){ if(e.which != 13) return; e.preventDefault(); return false; });

        // add behaviour to enable all inputs before submitting
        jQuery('form').submit(function(){
            jQuery(this).find('input').removeAttr('disabled');
        });
    });

</script>
