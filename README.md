# App Link Generator - 機能詳細ドキュメント

## 概要

**App Link Generator** は、WordPressのブロックエディタ(Gutenberg)に対応したプラグインで、App StoreとGoogle Play Storeのアプリ情報を美しく表示するためのブロックを提供します。アプリ検索機能により、アプリ情報を自動取得して簡単に挿入できます。

- **バージョン**: 1.1.0
- **必要環境**: WordPress 5.8以上、PHP 7.4以上
- **ブロックAPI**: Version 2

---

## 主要機能

### 1. ブロックエディタ対応
- Gutenbergブロックとして「アプリストアリンク」ブロックを提供
- ブロック名: `app-store-links/download-buttons`
- カテゴリ: ウィジェット
- アイコン: スマートフォン

### 2. アプリ検索機能
- **iOSアプリ検索**: iTunes Search APIを使用してApp Storeのアプリを検索
- **Androidアプリ検索**: Google Play Storeをスクレイピングしてアプリを検索
- 検索結果は2カラムで表示(iOS/Android)
- 各検索結果には以下の情報を表示:
  - アプリアイコン
  - アプリ名
  - 開発者名
  - 価格

### 3. アプリ情報の自動取得
検索結果からアプリを選択すると、以下の情報が自動的に取得・設定されます:

- アプリ名 (`appName`)
- アプリアイコン (`iconUrl`)
- 開発者名 (`developer`)
- 価格 (`price`)
- ストアURL (`appStoreUrl` / `googlePlayUrl`)
- 評価(星) (`rating`)
- レビュー数 (`reviewCount`)
- トラックID (`trackId`)
- ストア種別 (`store`: `ios` / `google_play`)
- 最終更新日 (`lastUpdated`)

### 4. 表示カスタマイズ
- App Storeリンクの表示/非表示切り替え
- Google Playリンクの表示/非表示切り替え
- 各ストアのリンクを個別に制御可能

### 5. 公式バッジ表示
- App Store公式バッジ: `https://nabettu.github.io/appreach/img/itune_ja.svg`
- Google Play公式バッジ: `https://nabettu.github.io/appreach/img/gplay_ja.png`

### 6. レスポンシブデザイン
- モバイル・タブレット・デスクトップに対応
- アイコンとテキストが適切に配置される流動的なレイアウト

### 7. 自動更新機能(キャッシュ管理)
- **キャッシュ期間**: 24時間
- **日次Cron**: 毎日自動的にアプリ情報を更新
- **レジストリ管理**: 使用中のアプリIDをデータベースに登録
- **Transient API**: WordPressのTransient APIを使用してキャッシュ管理

---

## ファイル構成

```
app-store-links/
├── app-store-links.php          # メインプラグインファイル
├── includes/                    # バックエンド処理
│   ├── GooglePlayScraper.php    # Google Playスクレイパー
│   ├── AppStoreScraper.php      # App Storeスクレイパー
│   └── AppStoreLinks_Manager.php # キャッシュ・自動更新管理
├── src/                         # ソースファイル(ビルド前)
│   ├── block.json              # ブロック定義
│   ├── index.js                # ブロック登録
│   ├── edit.js                 # エディタコンポーネント
│   ├── render.php              # フロントエンド表示テンプレート
│   ├── style.scss              # フロントエンドスタイル
│   └── editor.scss             # エディタスタイル
├── build/                       # ビルド済みファイル
│   ├── block.json
│   ├── index.js
│   ├── index.css
│   ├── style-index.css
│   ├── render.php
│   └── index.asset.php
├── package.json                 # npm依存関係
├── style.css                    # コンパイル済みスタイル
├── editor.css                   # コンパイル済みエディタスタイル
└── README.md                    # プラグイン説明
```

---

## 技術詳細

### ブロック属性

| 属性名 | 型 | デフォルト | 説明 |
|--------|-----|-----------|------|
| `appName` | string | "" | アプリ名 |
| `iconUrl` | string | "" | アプリアイコンURL |
| `developer` | string | "" | 開発者名 |
| `price` | string | "" | 価格 |
| `appStoreUrl` | string | "" | App Store URL |
| `googlePlayUrl` | string | "" | Google Play URL |
| `showAppStore` | boolean | true | App Storeリンク表示フラグ |
| `showGooglePlay` | boolean | true | Google Playリンク表示フラグ |
| `rating` | number | 0 | 評価(0-5) |
| `reviewCount` | string | "" | レビュー数 |
| `trackId` | string | "" | アプリID |
| `store` | string | "" | ストア種別 |
| `lastUpdated` | string | "" | 最終更新日 |

### REST APIエンドポイント

#### 1. `/app-store-links/v1/search`
- **メソッド**: GET
- **パラメータ**: `term` (検索キーワード)
- **機能**: Google Play Storeでアプリを検索
- **戻り値**: アプリ情報の配列

#### 2. `/app-store-links/v1/lookup`
- **メソッド**: GET
- **パラメータ**: `id` (アプリID)
- **機能**: Google Play Storeから詳細情報を取得
- **戻り値**: アプリ詳細情報

### スクレイパー機能

#### GooglePlayScraper.php
- **search($term)**: Google Playでアプリを検索
  - HTMLをパースして検索結果を抽出
  - 最大5件の結果を返す
  - アプリ名、アイコン、開発者名、URLを取得
  
- **get_details($id)**: アプリIDから詳細情報を取得
  - アプリ名、開発者名、アイコン、評価、レビュー数を取得
  - DOMXPathを使用してHTMLをパース
  - 複数のフォールバック戦略で情報を抽出

#### AppStoreScraper.php
- **get_details($id)**: iTunes Lookup APIを使用
  - 公式APIから正確な情報を取得
  - レビュー数を日本語形式にフォーマット(例: "1.5万件")

### キャッシュ管理(AppStoreLinks_Manager.php)

#### 主要メソッド

1. **init()**: Cronフックを登録
2. **get_app_data($attributes)**: キャッシュからデータを取得
   - Transientキャッシュをチェック
   - キャッシュがあれば属性にマージ
   - キャッシュがなければ元の属性を返す
3. **register_app($id, $store)**: アプリをレジストリに登録
   - 新規アプリの場合、初回フェッチを実行
4. **fetch_and_cache($id, $store)**: データを取得してキャッシュ
   - ストア種別に応じてスクレイパーを選択
   - 取得したデータを24時間キャッシュ
5. **cron_update_all()**: 日次Cronジョブ
   - レジストリ内の全アプリ情報を更新

### フロントエンド表示(render.php)

- AppReachスタイルのレイアウトを採用
- 以下の要素を表示:
  - アプリアイコン(左側、丸角)
  - アプリ名(太字)
  - 開発者名
  - 価格
  - 星評価(5段階、視覚的表示)
  - レビュー数
  - ストアバッジ(App Store/Google Play)
  - 最終更新日(右下に絶対配置)
  - "posted with テクノフィア"リンク

### エディタコンポーネント(edit.js)

#### 検索インターフェース
- 検索ボックスとボタン
- iOS/Android検索結果を2カラムで表示
- 各結果はクリック可能なカード形式

#### プレビュー
- フロントエンドと同じAppReachスタイル
- リアルタイムプレビュー

#### サイドバーコントロール
- **アプリ詳細パネル**: 手動編集可能
  - アプリ名、アイコンURL、開発者、価格、評価、レビュー数
- **ストアリンクパネル**: 表示制御
  - App Store URL、表示トグル
  - Google Play URL、表示トグル

### スタイル(style.scss)

- **`.appreach`**: メインコンテナ
  - 白背景、シャドウ、丸角
- **`.appreach__icon`**: アイコン
  - 左フロート、17.5%丸角、最大幅120px
- **`.appreach__detail`**: 詳細情報エリア
- **`.appreach__star`**: 星評価
  - 相対配置で2層構造(ベース/評価)
  - 評価部分は幅で制御(例: 80% = 4星)
- **`.appreach__date`**: 更新日
  - 絶対配置(右下)
- **`.appreach__links`**: ストアバッジ
  - 高さ40px、横並び

---

## ワークフロー

### 1. プラグイン有効化時
1. Cronジョブをスケジュール(`app_store_links_daily_update`)
2. 毎日1回、登録済みアプリ情報を自動更新

### 2. ブロック挿入時
1. ユーザーがアプリ名を検索
2. iTunes APIとGoogle Play Scraperが並列検索
3. 検索結果を2カラムで表示

### 3. アプリ選択時
1. 選択されたアプリの詳細情報を取得
   - iOS: iTunes APIから直接取得
   - Android: `/lookup`エンドポイント経由でスクレイピング
2. ブロック属性を更新
3. エディタでプレビュー表示

### 4. フロントエンド表示時
1. `render.php`が呼び出される
2. `AppStoreLinks_Manager::get_app_data()`でキャッシュチェック
3. キャッシュがあれば最新情報を使用
4. AppReachスタイルでレンダリング

### 5. 日次更新
1. Cronジョブが実行
2. レジストリ内の全アプリをループ
3. 各アプリの情報を再取得してキャッシュ更新

---

## 特徴的な機能

### 1. ハイブリッド検索
- **iOS**: 公式iTunes Search APIを使用(高速・正確)
- **Android**: Google Playをスクレイピング(公式API不使用)

### 2. インテリジェントなキャッシュ
- 初回表示時にデータを取得・キャッシュ
- 24時間キャッシュで高速表示
- Cronで自動更新し、常に最新情報を保持

### 3. レジストリシステム
- 使用中のアプリIDをデータベースに保存
- Cronジョブで一括更新
- 不要なAPI呼び出しを削減

### 4. フォールバック戦略
- Google Playスクレイパーは複数の抽出方法を試行
- HTML構造変更に対する耐性

### 5. 日本語対応
- レビュー数を日本語形式で表示(例: "1.5万件")
- 日本のストア(JP)を優先検索
- 日本語バッジ画像を使用

---

## 依存関係

### npm パッケージ
- `@wordpress/block-editor`: ^12.0.0
- `@wordpress/blocks`: ^12.0.0
- `@wordpress/components`: ^23.0.0
- `@wordpress/element`: ^5.0.0
- `@wordpress/i18n`: ^4.0.0
- `@wordpress/scripts`: ^26.0.0

### WordPress機能
- Block API v2
- REST API
- Transient API
- Cron API
- wp_remote_get()

---

## ビルドコマンド

```bash
# 開発モード(ウォッチ)
npm run start

# プロダクションビルド
npm run build
```

---

## セキュリティ

- 直接アクセス防止(`ABSPATH`チェック)
- REST APIパーミッションコールバック設定
- `esc_url()`, `esc_attr()`, `esc_html()`でエスケープ処理
- `wp_remote_get()`でHTTPリクエスト
- User-Agentヘッダー設定

---

## まとめ

App Link Generatorは、WordPressブロックエディタでアプリストアリンクを美しく表示するための包括的なソリューションです。自動検索、キャッシュ管理、日次更新により、常に最新のアプリ情報を手間なく表示できます。AppReachスタイルの洗練されたデザインと、iOS/Android両対応により、モバイルアプリの紹介に最適なツールとなっています。