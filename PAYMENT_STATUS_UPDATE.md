# Hướng dẫn cập nhật Trạng thái Thanh toán Cổ tức

## Tóm tắt thay đổi

Hệ thống đã được cập nhật để phân biệt rõ ràng giữa hai loại thanh toán cổ tức:
1. **Chưa lưu ký** → **Mặc định đã thanh toán** (tự động)
2. **Đã lưu ký** → **Mặc định chưa thanh toán** (cần thanh toán thủ công)

## Các trạng thái thanh toán

| Trạng thái | Mã | Ý nghĩa |
|-----------|-----|--------|
| Đã trả (chưa LK) | `paid_not_deposited` | Đã thanh toán cho cổ đông chưa lưu ký (**tự động**) |
| Đã trả (đã LK) | `paid_deposited` | Đã thanh toán cho cổ đông đã lưu ký (**thủ công**) |
| Chưa trả | `unpaid` | Chưa thanh toán (**cần xử lý thủ công**) |

## Quy tắc tự động xác định trạng thái khi import

Khi import dữ liệu nhà đầu tư, hệ thống sẽ tự động xác định trạng thái thanh toán:

- **Nếu có cổ phiếu chưa lưu ký** (`not_deposited_quantity > 0`)
  → Trạng thái = `paid_not_deposited` ✅ (**mặc định đã thanh toán**)

- **Nếu chỉ có cổ phiếu đã lưu ký** (`not_deposited_quantity = 0` và `deposited_quantity > 0`)
  → Trạng thái = `unpaid` ❌ (cần **thanh toán thủ công**)

- **Nếu không có cả hai** (không có dữ liệu số lượng)
  → Trạng thái = `unpaid` (mặc định)

## Các files đã được cập nhật

### Database
- ✅ `/database/migrations/2025_08_08_000001_create_dividend_records_table.php` - Cập nhật ENUM payment_status
- ✅ `/database/migrations/2025_10_31_000000_update_payment_status_enum.php` - Migration để cập nhật dữ liệu hiện có

### Import Logic
- ✅ `/app/Imports/InvestorsImport.php` - Cập nhật logic xác định trạng thái khi UPDATE và INSERT

### Controllers
- ✅ `/app/Http/Controllers/Admin/Securities/DividendController.php` - Cập nhật lọc dữ liệu thanh toán
- ✅ `/app/Http/Controllers/Admin/Securities/DividendRecordController.php` - Cập nhật lọc dữ liệu paid/unpaid

### Views
- ✅ `/resources/views/admin/securities/dividend/details.blade.php` - Cập nhật hiển thị trạng thái

## Hướng dẫn thực hiện

### Bước 1: Chạy Migration
```bash
php artisan migrate
```

### Bước 2: Cập nhật dữ liệu hiện có
Migration sẽ tự động cập nhật tất cả các bản ghi có `payment_status = 'paid'` sang `'paid_deposited'`

### Bước 3: Kiểm tra dữ liệu
```bash
php artisan tinker
DividendRecord::select('payment_status')->groupBy('payment_status')->get();
```

## Ví dụ

### Ví dụ 1: Nhà đầu tư chỉ có cổ phiếu chưa lưu ký
```
SID: ABC123
not_deposited_quantity: 100
deposited_quantity: 0
→ Trạng thái = "paid_not_deposited" ✅ (tự động đã thanh toán)
```

### Ví dụ 2: Nhà đầu tư chỉ có cổ phiếu đã lưu ký
```
SID: XYZ789
not_deposited_quantity: 0
deposited_quantity: 200
→ Trạng thái = "unpaid" ❌ (chưa thanh toán, cần xử lý thủ công)
```

### Ví dụ 3: Nhà đầu tư có cả hai loại cổ phiếu
```
SID: MIX456
not_deposited_quantity: 50
deposited_quantity: 150
→ Trạng thái = "paid_not_deposited" ✅ (tự động đã thanh toán vì có chưa LK)
```

## Quy trình thanh toán

### Cho cổ đông chưa lưu ký:
1. ✅ Import → Tự động đánh dấu `paid_not_deposited`
2. ✅ Không cần xử lý thêm

### Cho cổ đông đã lưu ký:
1. ❌ Import → Tự động đánh dấu `unpaid`
2. 🖱️ Cần vào giao diện **Thanh toán cổ tức** để xử lý thủ công
3. ✅ Cập nhật trạng thái thành `paid_deposited`

## Ghi chú
- Trạng thái được xác định dựa trên số lượng cổ phiếu, không phải số tiền
- Việc thanh toán từ giao diện quản trị sẽ cập nhật trạng thái thành `'paid_deposited'` (mặc định)
- Cấu trúc này giúp phân biệt rõ ràng giữa hai loại thanh toán tự động và thủ công
