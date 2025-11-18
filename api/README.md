# API 后端文档

## 概述

这是特定技能職員管理システムのバックエンドAPIです。Fat-Free Framework (F3) を使用しています。

## 技術スタック

- **フレームワーク**: Fat-Free Framework (F3)
- **データベース**: SQLite
- **PHPバージョン**: 8.3+
- **認証**: JWTトークン（セッションベース）

## ディレクトリ構造

```
api/
├── lib/
│   └── base.php          # F3フレームワークコア
├── controllers/          # コントローラー
│   ├── AuthController.php
│   └── UserController.php
├── models/               # データモデル
├── services/             # ビジネスロジック
├── schema/               # データベーススキーマ
├── index.php             # アプリケーションエントリーポイント
├── config.php            # 設定ファイル
├── setup.php             # データベース初期化スクリプト
└── README.md             # このファイル
```

## ローカル環境での実行

### 前提条件

- PHP 8.3以上
- SQLite3拡張
- Composer（推奨）

### セットアップ手順

1. **環境変数の設定**
   ```bash
   cp ../.env.example ../.env
   # .envファイルを編集して必要な設定を行う
   ```

2. **データベースの初期化**
   ```bash
   php setup.php
   ```

3. **開発サーバーの起動**
   ```bash
   php -S localhost:8000 -t api
   ```

4. **APIのテスト**
   ```bash
   curl http://localhost:8000/
   curl http://localhost:8000/api/health
   ```

## APIエンドポイント

### 認証関連

| メソッド | エンドポイント | 説明 |
|---------|---------------|------|
| POST | `/api/auth/login` | ログイン |
| POST | `/api/auth/logout` | ログアウト |
| GET | `/api/auth/me` | 現在のユーザー情報 |

### ユーザー管理

| メソッド | エンドポイント | 説明 |
|---------|---------------|------|
| GET | `/api/users` | ユーザー一覧 |
| POST | `/api/users` | ユーザー作成 |
| GET | `/api/users/{id}` | ユーザー詳細 |
| PUT | `/api/users/{id}` | ユーザー更新 |
| DELETE | `/api/users/{id}` | ユーザー削除 |

### 文書管理

| メソッド | エンドポイント | 説明 |
|---------|---------------|------|
| GET | `/api/documents` | 文書一覧取得 |
| POST | `/api/documents` | 文書アップロード |
| GET | `/api/documents/{id}` | 文書詳細取得 |
| PUT | `/api/documents/{id}` | 文書更新 |
| DELETE | `/api/documents/{id}` | 文書削除 |
| GET | `/api/documents/{id}/download` | 文書ダウンロード |
| GET | `/api/documents/{id}/check-expiry` | 有効期限チェック |
| GET | `/api/documents/expiring` | 期限間近の文書一覧 |
| GET | `/api/documents/expired` | 期限切れの文書一覧 |
| POST | `/api/documents/update-expiry-statuses` | 有効期限ステータス一括更新 |

詳細は [DOCUMENT_API.md](DOCUMENT_API.md) を参照してください。

### システム関連

| メソッド | エンドポイント | 説明 |
|---------|---------------|------|
| GET | `/` | API情報 |
| GET | `/api/health` | ヘルスチェック |

## 認証

APIはセッションベースの認証を使用しています。ログイン後に返されるトークンを`Authorization`ヘッダーに含めてください：

```
Authorization: Bearer {token}
```

## エラーレスポンス

すべてのエラーはJSON形式で返されます：

```json
{
  "error": "エラーメッセージ",
  "code": 400,
  "timestamp": "2024-01-01T12:00:00+00:00",
  "path": "/api/endpoint"
}
```

## データベーススキーマ

主要なテーブル：

- `users` - ユーザー情報（ログインアカウント管理）
- `employees` - 従業員情報（特定技能外国人従業員の基本情報）
- `certificates` - 資格証明書（資格・免許・証明書の管理）
- `work_records` - 勤怠記録（日々の勤務記録・シフト管理）
- `documents` - 書類管理（在留カード・契約書等の書類）
- `system_logs` - システムログ（監査証跡）
- `sessions` - セッション管理（ログインセッション）

詳細なスキーマ情報は [schema/README.md](schema/README.md) を参照してください。

### 主な機能

- **自動タイムスタンプ更新**: updated_atカラムは更新時に自動的に現在時刻に更新されます（トリガー）
- **外部キー制約**: データ整合性のためのカスケード削除・NULL設定
- **CHECK制約**: ENUM相当の値制限（role、status、gender等）
- **ユニーク制約**: 重複防止（employee_number、username、work_date等）
- **インデックス**: 検索パフォーマンス向上のための最適化

## 開発ガイドライン

### コントローラーの作成

1. `controllers/`ディレクトリに新しいコントローラーファイルを作成
2. クラス名は`{Name}Controller`とする
3. HTTPメソッドに対応するメソッドを実装（`get()`, `post()`, `put()`, `delete()`）

例：
```php
<?php
class ExampleController {
    public function get() {
        // GETリクエストの処理
    }
    
    public function post() {
        // POSTリクエストの処理
    }
}
```

### ルートの追加

`index.php`に新しいルートを追加：

```php
$f3->route('GET /api/example', 'ExampleController->get');
$f3->route('POST /api/example', 'ExampleController->post');
```

### データベースアクセス

PDOを使用してデータベースにアクセス：

```php
$db = $f3->get('DB');
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
```

## セキュリティ

- すべての入力はバリデーションが必要
- SQLインジェクション対策としてプリペアドステートメントを使用
- XSS対策として出力のエスケープが必要
- 認証されたユーザーのみがアクセスできるエンドポイントには認証チェックを実装

## ログ

エラーや重要な操作はログに記録されます：

```php
error_log('エラーメッセージ', 0);
```

## 共有ホストへのデプロイ

1. すべてのファイルをサーバーにアップロード
2. `.env`ファイルを設定
3. `setup.php`を実行してデータベースを初期化
4. ファイルのパーミッションを設定：
   ```bash
   chmod 755 api/
   chmod 644 api/*.php
   chmod 777 data/
   chmod 777 data/uploads/
   ```

## トラブルシューティング

### よくある問題

1. **500 Internal Server Error**
   - PHPエラーログを確認
   - `.htaccess`の設定を確認
   - ファイルパーミッションを確認

2. **データベース接続エラー**
   - SQLiteファイルのパスを確認
   - ディレクトリの書き込み権限を確認

3. **認証エラー**
   - セッション設定を確認
   - トークンの有効期限を確認

### デバッグモード

`.env`ファイルでデバッグモードを有効に：

```
APP_DEBUG=true
```

## 貢献

1. 新機能の追加はブランチを作成して開発
2. コードはPSR-12規約に従う
3. テストを作成して動作確認
4. プルリクエストを作成してレビュー依頼

## ライセンス

MIT License