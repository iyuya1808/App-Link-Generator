# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

App Link Generator は、WordPress ブロックエディタ向けのプラグイン。App Store と Google Play のアプリリンクをブロック形式で表示する。テキストドメイン: `app-link-generator`、関数/クラスプレフィックス: `applige_` / `APPLIGE_`。

## ビルドコマンド

```bash
# JavaScript のビルド（src/ → build/）
npm run build

# 開発時のウォッチモード
npm run start
```

ビルド後は `build/` ディレクトリに出力される。`build/` はコミット対象。

## アーキテクチャ

### ブロック構成（動的ブロック）

- `src/edit.js` — React ブロックエディタ UI（検索・選択 UI）
- `src/render.php` → `build/render.php` — フロントエンドをサーバーサイドで描画
- `src/block.json` — ブロック属性定義
- `save: () => null` — 保存関数は null（描画は常に PHP 側）

### PHP クラス

| ファイル | 役割 |
|---|---|
| `includes/AppStoreScraper.php` | iTunes Search API を叩いて iOS アプリ情報を取得 |
| `includes/GooglePlayScraper.php` | Google Play の HTML を DOM パースしてアプリ情報を取得 |
| `includes/AppStoreLinks_Manager.php` | WordPress transient でキャッシュ（TTL 24h）管理、cron 連携 |

### REST API エンドポイント

- `POST /wp-json/app-link-generator/v1/search` — iOS + Android 両方を検索
- `POST /wp-json/app-link-generator/v1/lookup` — アプリ詳細情報取得

### データフロー

```
ブロックエディタ (edit.js)
  → REST API でアプリ検索 → setAttributes() に保存

フロントエンド表示
  → render.php → APPLIGE_Manager::get_app_data()
    → transient キャッシュ確認 → なければスクレイパーで取得
    → HTML 出力（esc_html / esc_url でエスケープ済み）
```

### キャッシュ

transient キー形式: `applige_{store}_{app_id}`（例: `applige_ios_123456`）

### cron

プラグイン有効化時に `applige_daily_update` を登録（毎日実行）。無効化時に削除。

## i18n

- 翻訳ファイル: `languages/app-link-generator-ja.*`
- PHP: `load_plugin_textdomain('app-link-generator')`
- JS: `wp_set_script_translations()` でエディタスクリプトに適用
- `.po` を編集後、`.mo` にコンパイルする（`msgfmt` コマンドまたは Poedit）

## テスト

テストスイートは存在しない。動作確認は WordPress 環境で直接行う。
