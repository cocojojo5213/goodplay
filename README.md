# 特定技能職員管理システム

特定技能外国人従業員の情報を管理するためのWebアプリケーションです。

## 技術スタック

- **バックエンド**: Fat-Free Framework (F3) + PHP 8.3
- **フロントエンド**: Vue 3 + Vite + Tailwind CSS
- **データベース**: SQLite
- **状態管理**: Pinia
- **ルーティング**: Vue Router
- **国際化**: Vue I18n
- **HTTPクライアント**: Axios

## プロジェクト構造

```
goodplay/
├── api/                     # バックエンド - F3フレームワーク
│   ├── lib/
│   │   └── base.php        # F3フレームワークファイル
│   ├── index.php           # プログラムエントリーポイント
│   ├── config.php          # 設定ファイル
│   ├── setup.php           # データベース初期化スクリプト
│   ├── controllers/        # ルートハンドラー
│   │   ├── AuthController.php
│   │   └── UserController.php
│   ├── models/             # データモデル
│   ├── services/           # ビジネスロジックサービス
│   ├── schema/             # データベースSQLスクリプト
│   └── README.md           # バックエンド説明
│
├── src/                    # フロントエンド - Vue 3
│   ├── main.js            # エントリーファイル
│   ├── App.vue            # ルートコンポーネント
│   ├── components/        # 再利用可能コンポーネント
│   ├── views/             # ページコンポーネント
│   │   ├── Login.vue
│   │   ├── Dashboard.vue
│   │   ├── Employees.vue
│   │   ├── Documents.vue
│   │   └── Settings.vue
│   ├── stores/            # Pinia状態管理
│   │   └── auth.js
│   ├── router/            # Vue Router設定
│   │   └── index.js
│   ├── i18n/              # 国際化（日本語）
│   │   ├── index.js
│   │   └── messages/
│   │       └── ja.json
│   ├── assets/            # 静的リソース
│   └── styles/            # スタイルファイル
│       └── main.css
│
├── dist/                  # フロントエンドビルド出力（デプロイ用）
├── data/                  # データディレクトリ
│   └── uploads/           # アップロードファイル保存
│
├── .env.example           # 環境変数テンプレート
├── .gitignore             # Git無視ファイル
├── package.json           # npm依存設定
├── vite.config.js         # Viteビルド設定
├── tailwind.config.js     # Tailwind CSS設定
├── .htaccess              # Apacheルーティング設定
└── README.md              # プロジェクト説明
```

## 前提条件

- Node.js 18.x 以上
- PHP 8.3 以上
- SQLite3 拡張
- Composer（推奨）

## インストールとセットアップ

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd goodplay
```

### 2. 環境変数の設定

```bash
cp .env.example .env
```

`.env`ファイルを編集して必要な設定を行います：

```env
# データベース
DB_PATH=./data/database.sqlite

# アプリケーション
APP_NAME=特定技能職員管理システム
APP_DEBUG=false
APP_TIMEZONE=Asia/Tokyo
BASE_URL=http://localhost:8000

# セッション
SESSION_TIMEOUT=3600
SESSION_SALT=your-random-salt-here

# 管理者初期化
ADMIN_USERNAME=admin
ADMIN_PASSWORD=defaultpassword123

# 共有ホスト
WEB_ROOT=/home/eiwakai/public_html
```

### 3. フロントエンドのセットアップ

```bash
# 依存関係のインストール
npm install

# 開発サーバーの起動
npm run dev
```

フロントエンド開発サーバーは `http://localhost:3000` で起動します。

### 4. バックエンドのセットアップ

```bash
# データベースの初期化
php api/setup.php

# PHP開発サーバーの起動
php -S localhost:8000 -t api
```

バックエンドAPIサーバーは `http://localhost:8000` で起動します。

### 5. アプリケーションへのアクセス

ブラウザで `http://localhost:3000` にアクセスします。

デフォルトの管理者アカウント：
- ユーザー名: `admin`
- パスワード: `defaultpassword123`

## 開発コマンド

### フロントエンド

```bash
# 開発サーバー起動
npm run dev

# 本番用ビルド
npm run build

# ビルドプレビュー
npm run preview

# コード整形
npm run format

# リントチェック
npm run lint
```

### バックエンド

```bash
# PHP開発サーバー起動
php -S localhost:8000 -t api

# データベース再初期化
php api/setup.php

# エラーログ確認
tail -f logs/php_errors.log
```

## デプロイ

### Sakura共有ホストへのデプロイ

1. **ファイルのアップロード**
   ```
   /home/eiwakai/public_html/
   ├── api/
   ├── dist/
   ├── data/
   ├── .env
   ├── .htaccess
   └── ...
   ```

2. **パーミッション設定**
   ```bash
   chmod 755 api/
   chmod 644 api/*.php
   chmod 777 data/
   chmod 777 data/uploads/
   ```

3. **環境変数設定**
   - `.env`ファイルをサーバー環境に合わせて編集
   - `WEB_ROOT`を正しいパスに設定

4. **データベース初期化**
   ```bash
   php api/setup.php
   ```

5. **動作確認**
   - ブラウザでサイトにアクセス
   - ログイン機能を確認
   - APIエンドポイントを確認

## APIドキュメント

詳細なAPIドキュメントは [api/README.md](api/README.md) を参照してください。

## 主な機能

### 🔐 認証・認可
- ユーザーログイン・ログアウト
- 役割ベースのアクセス制御
- セッション管理

### 👥 従業員管理
- 従業員情報の登録・編集・削除
- ビザ・在留資格の管理
- 緊急連絡先の管理

### 📄 書類管理
- 各種書類のアップロード・管理
- 有効期限の管理
- カテゴリ分類

### 📊 勤怠管理
- 勤務記録の管理
- 労働時間の集計
- 残業時間の管理

### 📈 レポート機能
- 各種統計レポート
- データエクスポート
- グラフ表示

## ブラウザサポート

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## 貢献

1. Issueを作成して機能追加やバグ修正を提案
2. Forkして機能ブランチを作成
3. 変更をコミットしてプッシュ
4. Pull Requestを作成

## ライセンス

MIT License

## サポート

問題が発生した場合は、以下の方法でサポートを受けてください：

1. [Issues](../../issues)で問題を報告
2. [Wiki](../../wiki)でドキュメントを参照
3. メールで管理者に連絡

---

**開発チーム**: GoodPlay Team  
**最終更新**: 2024年1月