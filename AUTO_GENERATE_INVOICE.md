(You can add `echo "Script works";` in the file temporarily for testing.)

---

## ⏱ Step 2: Open Cron Jobs in cPanel

1. In the **cPanel Dashboard**, go to  
**Advanced → Cron Jobs**
2. Scroll down to **“Add New Cron Job”**

---

## ⚙️ Step 3: Set the Schedule

Choose when the script should run.  
Recommended: once daily at midnight.

| Field | Value |
|-------|--------|
| Minute | `0` |
| Hour | `0` |
| Day | `*` |
| Month | `*` |
| Weekday | `*` |

This means it runs **every day at 12:00 AM**.

---

## 💻 Step 4: Add the Command

In the **Command** box, enter:

```bash
/usr/local/bin/php -q /home/USERNAME/public_html/auto_generate_invoices.php > /dev/null 2>&1
