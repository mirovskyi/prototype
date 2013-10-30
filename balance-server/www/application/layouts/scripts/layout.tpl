<!DOCTYPE html>
<html>
<head>
    {$zend->render('head.tpl')}
</head>
<body>
    <div id="content">
        {$zend->layout()->content}
    </div>
    <div id="footer">
        {$zend->render('footer.tpl')}
    </div>

    <div id="dialog-overlay"></div>
    <div id="dialog-box">
        <div class="dialog-content">
            <div id="dialog-message"></div>
            <a href="#" class="button">Close</a>
        </div>
    </div>

    {if ($zend->getHelper('FlashMessenger')->hasMessages())}
        <script type="text/javascript">
            {foreach from=$zend->getHelper('FlashMessenger')->getMessages() item='message'}
            popup('{$message}');
            {/foreach}
        </script>
    {/if}

</body>
</html>