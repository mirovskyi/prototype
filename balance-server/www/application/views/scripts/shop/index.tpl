<div style="display: inline-block;">
    <div style="float: left; width: 400px;">
        <h2>Товары магазина</h2>
        {foreach from=$items item='item'}
        <p>
            {$item->getTitle()} ({$item->getName()}) - {$item->getPrice()}
        </p>
        {/foreach}
    </div>
    <div id="form-box" style="float: right;">
        <h2>Добавление нового товара</h2>
        {$newItemForm}
    </div>
</div>