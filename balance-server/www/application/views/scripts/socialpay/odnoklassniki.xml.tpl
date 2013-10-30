<?xml version="1.0" encoding="UTF-8"?>
{if $error}
    <ns2:error_response xmlns:ns2='http://api.forticom.com/1.0/'>
        <error_code>{$error.code}</error_code>
        <error_msg>{$error.msg}</error_msg>
    </ns2:error_response>
{else}
    <callbacks_payment_response xmlns="http://api.forticom.com/1.0/">
        true
    </callbacks_payment_response>
{/if}