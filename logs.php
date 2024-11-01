<div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>
    <h2>
        StreamIn Logs
            <span style="float:right; font-size: 14px">
                <a target="_blank" href="http://streamin.io">StreamIn</a>
            </span>
    </h2>

    <p>Log items:</p>

    <table id="streamin_logs_table" class="widefat" cellspacing="0">
        <thead>
        <tr>
            <th scope="coll" class="manage-column" style="width:15%">Webhook</th>
            <th scope="coll" class="manage-column" style="width:25%">User Key</th>
            <th scope="coll" class="manage-column" style="width:25%">Timestamp</th>
            <th scope="coll" class="manage-column">Message</th>
        </tr>
        </thead>
        <tbody>
        <?php if(empty($logs)) { ?>
        <tr><td colspan="4">There are no log items</td></tr>
        <?php } else foreach($logs as $log) { ?>
        <tr>
            <td><b><?php echo $STREAMIN_WEBHOOKS[$log->hook]['title']; ?></b></td>
            <td><?php echo $log->user_key; ?></td>
            <td><?php echo $log->created_at; ?></td>
            <td><?php echo ($log->message == "SUCCESS") ? "<span style='color:#008855'>{$log->message}</span>" : "<span style='color:tomato'>{$log->message}</span>"; ?></td>
        </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
            <th scope="coll" class="manage-column">Webhook</th>
            <th scope="coll" class="manage-column">User Key</th>
            <th scope="coll" class="manage-column">Timestamp</th>
            <th scope="coll" class="manage-column">Message</th>
        </tr>
        </tfoot>
    </table>

    <br />

    <?php if(!empty($logs)) { ?>
    <input onclick="window.location = '<?php echo get_bloginfo('wpurl'); ?>/wp-admin/index.php?streamin_action=clear_log'" type="button" value="Clear Log" class="button action" title="Clear StreamIn log items" />
    <?php } ?>

</div>
