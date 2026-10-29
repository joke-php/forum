# Модуль форума

---
В разработке
---

MVP (Минимально жизнеспособный продукт) модуля форума

После выполнения SQL скрипта форума, где `:root_email` подставьте email администратора:

```sql
SET @root_role_id = (SELECT id FROM role WHERE module = 'forum' AND name = 'root' LIMIT 1);
SET @root_id = (SELECT id FROM user WHERE email = :root_email LIMIT 1);

INSERT IGNORE INTO user_role (user_id, role_id) VALUE (@root_id, @root_role_id);
```


