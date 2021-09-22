<div class="control-group">
    <label class="control-label" for="paymenterio_shop_id">Identyfikator bądź Hash sklepu</label>
     <div class="controls">
         <input type="text" name="payment_data[processor_params][paymenterio_shop_id]" id="paymenterio_shop_id" value="{$processor_params.paymenterio_shop_id}" required />
     </div>
</div>

<div class="control-group">
    <label class="control-label" for="paymenterio_api_key">Klucz API</label>
     <div class="controls">
         <input type="password" name="payment_data[processor_params][paymenterio_api_key]" id="paymenterio_api_key" value="{$processor_params.paymenterio_api_key}" required />
     </div>
</div>