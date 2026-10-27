{%layout main%}
<form action="/user/register" method="post" class="form form_width-small">
    {%if message%}
    <div class="message message_success">
        {{message}}
    </div>
    {%endif%}
    {%if errors%}
    <div class="message message_error">
        {%foreach error in errors%}
        <div>{{error}}</div>
        {%endforeach%}
    </div>
    {%endif%}
    <label class="form__row">
        <input type="email" name="email" placeholder="Введите адрес электронной почты" required class="form__input"
               value="{{email}}" autocomplete="on">
    </label>
    <label class="form__row">
        <input type="text" name="nickname" placeholder="Введите Ник, для отображения на форуме"
               value="{{nickname}}" required class="form__input" autocomplete="on">
    </label>
    <label class="form__row form__checkbox">
        <input type="checkbox" name="accept" value="accept" autocomplete="on" class="form__checkbox-input">
        <span class="form__checkbox-label">Соглашаюсь с правилами обработки персональных данных и правилами форума</span>
    </label>
    <div class="form__buttons">
        <button type="submit" class="btn btn_primary">Зарегистрироваться</button>
    </div>
    <input type="hidden" name="csrf_token" value="{% csrf %}">
</form>
{%endlayout%}