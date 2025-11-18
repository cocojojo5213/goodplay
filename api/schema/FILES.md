# スキーマ関連ファイル一覧

## ディレクトリ構造

```
api/schema/
├── schema.sql           # メインDDLファイル（全テーブル・インデックス・トリガー定義）
├── README.md            # 詳細スキーマドキュメント
├── CHANGELOG.md         # バージョン履歴と変更内容
└── FILES.md             # このファイル（ファイル一覧）
```

## ファイル説明

### schema.sql
**目的**: データベーススキーマの完全な定義
**内容**:
- 7つのテーブル定義（users, employees, certificates, work_records, documents, system_logs, sessions）
- 38個のインデックス
- 5つのトリガー（updated_at自動更新）
- CHECK制約（ENUM相当の値制限）
- 外部キー制約（CASCADE削除/SET NULL）
- UNIQUE制約（重複防止）

**使用方法**:
```bash
# メモリ内で構文検証
sqlite3 :memory: < api/schema/schema.sql

# 既存DBに適用
sqlite3 data/database.sqlite < api/schema/schema.sql

# setup.phpから自動適用される
php api/setup.php
```

### README.md
**目的**: スキーマの詳細ドキュメント
**内容**:
- 各テーブルの詳細仕様（全カラム、制約、インデックス）
- トリガーの説明
- 外部キー関係図
- CHECK制約一覧
- データ整合性ルール
- パフォーマンス最適化のヒント
- セットアップ手順
- メンテナンス方法

**対象読者**: 開発者、DBA、システム管理者

### CHANGELOG.md
**目的**: スキーマ変更履歴の記録
**内容**:
- バージョン1.0の詳細な変更内容
- 各テーブルの追加カラムと制約
- 新規追加されたインデックス
- トリガーの実装詳細
- 初期データの内容
- マイグレーション手順
- テストチェックリスト

**対象読者**: プロジェクトマネージャー、開発者

### FILES.md（このファイル）
**目的**: schema/ディレクトリ内ファイルの案内
**内容**:
- ディレクトリ構造
- 各ファイルの目的と使用方法
- ファイル間の関係性

## ファイル間の関係

```
schema.sql ──┐
             ├──> README.md（詳細説明）
             └──> CHANGELOG.md（変更履歴）
                  └──> FILES.md（このファイル）
```

1. **schema.sql**: 実行可能なSQLスクリプト
2. **README.md**: schema.sqlの内容を人間が理解しやすい形式で説明
3. **CHANGELOG.md**: schema.sqlの変更履歴を時系列で記録
4. **FILES.md**: ドキュメント全体のナビゲーション

## 更新フロー

スキーマを変更する場合の推奨フロー:

1. **schema.sqlを編集**
   - テーブル定義を変更
   - インデックスを追加/削除
   - トリガーを追加/修正

2. **構文検証**
   ```bash
   sqlite3 :memory: < api/schema/schema.sql
   ```

3. **README.mdを更新**
   - 該当テーブルのドキュメントを更新
   - 新しい制約を追加
   - インデックス一覧を更新

4. **CHANGELOG.mdに追記**
   - 新バージョンセクションを追加
   - 変更内容を詳細に記述
   - マイグレーション手順を記載

5. **setup.phpを更新**（必要に応じて）
   - 新しいカラムに対応するサンプルデータを追加
   - バリデーションロジックを更新

## 関連ファイル

プロジェクト内の関連ファイル:

### api/setup.php
- schema.sqlを読み込んでDB初期化
- 初期データ（サンプルレコード）を投入
- 実行: `php api/setup.php`

### api/config.php
- データベース接続設定
- DB_PATH環境変数からパスを取得

### api/README.md
- バックエンドAPI全体のドキュメント
- schema/README.mdへのリンクを含む

### README.md（プロジェクトルート）
- プロジェクト全体のドキュメント
- データベーススキーマセクションでschema/を参照

## バージョン管理

スキーマファイルはGitで管理されます：

```bash
# 変更を確認
git status api/schema/

# 変更をステージング
git add api/schema/

# コミット
git commit -m "chore(db): update schema - [変更内容の要約]"
```

## トラブルシューティング

### スキーマSQLのエラー
```bash
# 詳細なエラーメッセージを表示
sqlite3 :memory: < api/schema/schema.sql 2>&1
```

### setup.phpの実行エラー
```bash
# PHPエラーを確認
php api/setup.php 2>&1
```

### ドキュメントの不整合
1. schema.sqlの内容を確認
2. README.mdの該当箇所を更新
3. CHANGELOG.mdに変更を記録

## 今後の拡張

schema/ディレクトリに追加予定のファイル:

- **migrations/**: マイグレーションスクリプト
- **views.sql**: ビュー定義
- **functions.sql**: SQLite関数定義（UDF）
- **test_data.sql**: テスト用データセット
- **schema.png**: ER図（視覚的なスキーマ表現）

## 参考資料

- SQLite公式ドキュメント: https://www.sqlite.org/docs.html
- SQLite制約: https://www.sqlite.org/lang_createtable.html
- SQLiteトリガー: https://www.sqlite.org/lang_createtrigger.html
- SQLiteインデックス: https://www.sqlite.org/lang_createindex.html
