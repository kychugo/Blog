# 部署指南 | Deployment Guide

## Hugo 開發日誌 - 簡化部署流程

### 📦 需要上傳的檔案 | Files to Upload

上傳以下 4 個檔案到 `hugow.wuaze.com`：

1. **api.php** (29 KB) - 完整 API 後端
2. **init.php** (8 KB) - 資料庫初始化
3. **index.html** (54 KB) - 完整前端
4. **.htaccess** (285 bytes) - URL 重寫規則

### 🚀 部署步驟 | Deployment Steps

#### 第一步：上傳檔案
Upload 4 files to your PHP hosting at `hugow.wuaze.com`:
- api.php
- init.php  
- index.html
- .htaccess

#### 第二步：初始化資料庫
1. 在瀏覽器訪問：`https://hugow.wuaze.com/init.php`
2. 你應該看到 7 個資料表成功建立的訊息
3. 資料表列表：
   - users (用戶)
   - posts (文章)
   - tags (標籤)
   - post_tags (文章標籤關聯)
   - comments (評論)
   - likes (讚)
   - view_logs (瀏覽紀錄)

#### 第三步：驗證 API
訪問：`https://hugow.wuaze.com/api.php`

你應該看到 JSON 格式的 API 文檔，包含：
```json
{
  "name": "Hugo 開發日誌 API",
  "version": "1.0",
  "endpoints": { ... }
}
```

#### 第四步：開啟網站
訪問：`https://hugow.wuaze.com/` 或 `https://hugow.wuaze.com/index.html`

你應該看到博客主頁，包含：
- 深色科技風格設計
- 動畫背景
- 導航欄
- 勵志名言

#### 第五步：註冊第一個帳號（管理員）
1. 點擊 "登入 / Login" 按鈕
2. 切換到 "註冊 / Register" 標籤
3. 輸入：
   - Email
   - Username
   - Password (至少 8 個字符)
4. 點擊 "註冊 / Register"
5. **第一個註冊的用戶會自動成為管理員！**

#### 第六步：發佈第一篇文章
1. 以管理員身份登入後
2. 點擊 "新增文章 / Create Post" 按鈕
3. 填寫：
   - 標題 (Title)
   - 內容 (Content)
   - 標籤 (Tags, 用逗號分隔)
4. 點擊 "發佈 / Publish"

### ✅ 驗證清單 | Verification Checklist

- [ ] init.php 執行成功，顯示 7 個表創建完成
- [ ] api.php 顯示 API 文檔
- [ ] index.html 正確載入並顯示主頁
- [ ] 可以註冊新用戶
- [ ] 第一個用戶成為管理員
- [ ] 可以建立文章
- [ ] 可以瀏覽文章
- [ ] 可以留言
- [ ] 可以按讚
- [ ] 可以搜尋
- [ ] 可以按標籤篩選
- [ ] 可以按年份/月份篩選
- [ ] 語言切換功能正常 (繁中/英文)
- [ ] 管理員面板可以訪問
- [ ] 可以看到統計數據

### 🔒 安全建議 | Security Notes

1. **刪除 init.php**（建議）
   - 資料庫初始化完成後，可以刪除 `init.php` 以增加安全性
   - 或者重命名為 `init.php.backup`

2. **更改密鑰**（強烈建議）
   - 在 `api.php` 中找到這一行：
   ```php
   define('JWT_SECRET', 'hugo_blog_secret_key_2024_change_this_in_production');
   ```
   - 改為一個隨機字串

3. **更新 CORS 設定**（生產環境）
   - 在 `api.php` 中找到 CORS 設定
   - 將 `Access-Control-Allow-Origin: *` 改為你的實際域名

### 🛠️ 故障排除 | Troubleshooting

#### 問題：init.php 顯示資料庫連線錯誤
**解決方案：**
- 檢查資料庫憑證是否正確
- 確認資料庫服務器允許遠端連線
- 確認資料庫已創建

#### 問題：API 呼叫返回 CORS 錯誤
**解決方案：**
- 確認 .htaccess 已上傳
- 檢查伺服器是否支援 .htaccess
- 檢查 CORS 設定

#### 問題：文章無法創建
**解決方案：**
- 確認已以管理員身份登入
- 檢查瀏覽器控制台的錯誤訊息
- 確認資料表已正確創建

#### 問題：圖片/樣式無法載入
**解決方案：**
- index.html 是自包含的，所有 CSS 和 JS 都在檔案內
- 不需要外部資源
- 檢查檔案是否完整上傳

### 📁 檔案結構 | File Structure

```
hugow.wuaze.com/
├── api.php          # 主要 API 端點
├── init.php         # 資料庫初始化（可選刪除）
├── index.html       # 前端應用
└── .htaccess        # URL 重寫規則
```

### 🎯 功能測試順序 | Feature Testing Order

1. ✅ 資料庫初始化
2. ✅ 用戶註冊
3. ✅ 用戶登入
4. ✅ 創建文章（管理員）
5. ✅ 瀏覽文章
6. ✅ 留言
7. ✅ 按讚
8. ✅ 搜尋
9. ✅ 標籤篩選
10. ✅ 日期篩選
11. ✅ 語言切換
12. ✅ 管理員面板
13. ✅ 編輯文章
14. ✅ 刪除文章
15. ✅ 刪除用戶

### 💡 提示 | Tips

- **勵志名言** 會在每個頁面顯示：
  > "if you only know how to use a hammer, everything looks like a nail"

- **語言切換** 在導航欄右上角，點擊 "EN | 中" 按鈕

- **管理員面板** 只有管理員登入後才能看到

- **標籤** 在創建文章時用逗號分隔，例如：`PHP, MySQL, Blog`

- **搜尋功能** 會搜尋標題和內容

- **檢視紀錄** 管理員可以看到誰在什麼時候瀏覽了哪篇文章

### 📞 支援 | Support

如果遇到問題：
1. 檢查瀏覽器控制台的錯誤訊息
2. 檢查 PHP 錯誤日誌
3. 確認所有檔案都已正確上傳
4. 確認資料庫連線正常

---

**完成部署後，你就擁有一個功能完整的雙語博客平台！** 🎉

**After deployment, you'll have a fully functional bilingual blog platform!** 🎉
