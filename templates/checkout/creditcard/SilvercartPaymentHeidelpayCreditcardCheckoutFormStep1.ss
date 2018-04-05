{$CustomHtmlFormInitOutput}

<% if $PaymentMethod.mode == 'Dev' %>
<hr/>
<div class="alert alert-info">
    <strong><span class="icon icon-warning-sign fa fa-warning-sign"></span> <%t SilvercartPaymentHeidelpay.DevModeInfo 'This payment method is in TEST mode.' %></strong><br/>
    <br/>
    <strong><%t SilvercartPaymentHeidelpayCreditCard.DevModeData 'Test credit card data' %>:</strong><br/>
    <table>
        <tr>
            <td><%t SilvercartPaymentHeidelpayCreditCard.CardNumber 'Card number' %>:</td>
            <td>5453010000059543</td>
        </tr>
        <tr>
            <td><%t SilvercartPaymentHeidelpayCreditCard.CardType 'Card type' %>:</td>
            <td>MasterCard</td>
        </tr>
        <tr>
            <td><%t SilvercartPaymentHeidelpayCreditCard.CardExpiryDate 'Card expiry date' %>:</td>
            <td><%t SilvercartPaymentHeidelpayCreditCard.AnyDateInFuture 'any date in future' %></td>
        </tr>
        <tr>
            <td><%t SilvercartPaymentHeidelpayCreditCard.CVV 'CVV' %>:</td>
            <td>123</td>
        </tr>
    </table>
</div>
<hr/>
<% end_if %>


<form method="post" class="formular" id="paymentFrameForm">
    <input type="hidden" id="json-response" name="HeidelpayJsonResponse" />
    <iframe id="paymentIframe" src="{$PaymentFormUrl}" style="height:260px;"></iframe><br />
    <div class="control-group">
        <button class="btn btn-primary pull-right" id="" title="<%t SilvercartPaymentHeidelpayChannel.ConfirmAndBuy 'Confirm and buy' %>" type="submit"><%t SilvercartPaymentHeidelpayChannel.ConfirmAndBuy 'Confirm and buy' %></button>
    </div>
</form>
<% require javascript('silvercart-payment-heidelpay/client/js/SilvercartPaymentHeidelpayCreditCard.js') %>