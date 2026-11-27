# cricket-kit-shop

## Order images

Each buyer order can have one optional JPEG, PNG, or WebP image up to 5 MB. The image is stored under `uploads/order-images/`, and its relative path is saved in `buyers.image_path`. Customers can attach an image when placing an order or replace it when editing.

Existing databases add the `image_path` column automatically when the app connects. To add it manually in phpMyAdmin instead, run:

```sql
ALTER TABLE buyers ADD COLUMN image_path VARCHAR(255) NULL;
```
