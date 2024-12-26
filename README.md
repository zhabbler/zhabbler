# 嘰嘰喳喳# 嘰嘰喳喳
Jabbler 是一個社交網絡，試圖創建一個模仿 Tumblr 的簡單 CMS。此處提供的源代碼目前不穩定。

Tumblr 的擁有者是 David Karp，

您可以在[問題](https://github.com/zhabbler/zhabbler/issues)標籤中寫下您發現的任何錯誤或對新功能的建議。

## 實例
目前正在運行的 Jabbler 實例的列表

## 我可以建立自己的實例嗎？## 我可以建立自己的實例嗎？
當然！

但建議使用VDS或VPS，因為您將無法在主機上安裝Jubbler。


## 那麼如何安裝Jubbler？## 那麼如何安裝Jubbler？
1. 安裝 PHP 版本 >= 8.0、Apache、Composer、git、ffmpeg（用於處理影片）、npm (nodejs)。
2.安裝MySQL
   * 我們推薦使用 MariaDB，但任何 M
3.   * 我們推薦使用 MariaDB，但任何 M

4.4. 在您的網站所在目錄中安裝 Jabbler。
5. 前往要安裝 Jubbler 的網站的 Apache 配置，並將 `/Web/public` 新增至網站主資料夾的目錄中  前往要安裝 Jubbler 的網站的 Apache 配置，並將 `/Web/public` 新增至網站主資料夾的目錄中 

6.6. 前往 Jabbler 本身的配置 (`config.neon`) 並更改設定。
   * 在`encryption_key`中輸入隨機值（越多越好），因為該值用於加密金鑰   * 在`encryption_key`中輸入隨機值（越多越好），因為該值用於加密金鑰
 
7.7. 前往 `/Web/public/static/js/new_messenger.js` 並更改第一行的 URL。
8. 使用「composer install」指令安裝所有擴充功能。
10. 到 `/Web/public/static` 並安裝 到 `/Web/public/static` 並安裝

恭喜，您現在已在伺服器上安裝了 Jabbler！

管理員帳號是“admin@localhost.lh”，密碼是“qwerty123”。

如果安裝過程中遇到任何問題，[在Issues中寫](https://github.com/zhabbler/zhabbler/issues)

## 我可以從哪裡獲得幫助？
* 在我們的 [Telegram 聊天](https://t.me/Zhabbl/zhabbler/issues)
* 
