=== App Link Generator ===
Contributors: iyuya0623
Tags: app store, google play, mobile app, app link, block editor
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.2.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

App StoreとGoogle Play Storeのアプリインストールリンクをブロックエディタで簡単に表示できるプラグインです。

== Description ==

App Link Generator は、App StoreおよびGoogle Play Storeのモバイルアプリインストールリンクを簡単に表示できるWordPressプラグインです。WordPressブロックエディタ（Gutenberg）に完全対応しています。

**主な機能:**

* App StoreおよびGoogle Play Storeからアプリを検索・選択
* アイコン・アプリ名・開発者名・価格・評価などのアプリ情報を自動表示
* 各ストアの表示・非表示を個別にカスタマイズ可能
* パフォーマンス向上のためのアプリデータ自動キャッシュ
* アプリ情報の毎日自動更新
* ブロックエディタ（Gutenberg）対応

== 外部サービスの利用について ==

このプラグインはアプリ情報を取得するために外部サービスと通信します。本プラグインをご利用いただくことで、以下の内容に同意したものとみなします。

**iTunes Search API（Apple Inc.）**

* **用途**: iOSアプリの検索およびアプリメタデータ（名前・アイコン・価格・評価など）の取得
* **データ送信のタイミング**: ブロックエディタでアプリを検索したとき
* **送信データ**: 検索キーワード（アプリ名など）
* **サービス提供者**: Apple Inc.
* **利用規約**: https://www.apple.com/legal/internet-services/itunes/
* **プライバシーポリシー**: https://www.apple.com/legal/privacy/

**Google Playストア（Google LLC）**

* **用途**: Androidアプリの検索およびアプリ情報の取得
* **データ送信のタイミング**: ブロックエディタでアプリを検索したとき
* **送信データ**: 検索キーワード（アプリ名など）
* **仕組み**: Google PlayストアのWebページから公開情報を取得します
* **サービス提供者**: Google LLC
* **利用規約**: https://play.google.com/about/play-terms/
* **プライバシーポリシー**: https://policies.google.com/privacy

**注意事項:**

* ユーザーの個人情報はいかなる外部サービスにも送信されません
* サイト管理者が入力した検索キーワードのみが送信されます
* アプリ情報はローカルにキャッシュされ、外部リクエストを最小限に抑えます
* 本プラグインはユーザーの行動データを追跡・収集しません

== インストール ==

1. プラグインファイルを `/wp-content/plugins/app-link-generator` ディレクトリにアップロードするか、WordPressの「プラグイン」画面から直接インストールしてください。
2. WordPress管理画面の「プラグイン」からプラグインを有効化してください。
3. 投稿・固定ページのブロックエディタで「アプリリンクジェネレーター」ブロックを追加してください。

== よくある質問 ==

= アプリリンクを追加するには？ =

1. ブロックエディタで「＋」ボタンをクリックして新しいブロックを追加
2. 「アプリリンクジェネレーター」または「App Link Generator」で検索
3. 検索フィールドにアプリ名を入力
4. 検索結果からアプリを選択
5. アプリ情報が自動的に表示されます

= 表示デザインをカスタマイズできますか？ =

はい、CSSを使ってカスタマイズできます。プラグインはスタイリングに `appreach` クラスを使用しています。

= アプリ情報はどのくらいの頻度で更新されますか？ =

アプリ情報は毎日1回自動更新されます。ブロックエディタでアプリを再選択することで手動でも更新できます。

= クラシックエディタでも使えますか？ =

いいえ、本プラグインはブロックエディタ（Gutenberg）専用です。

== スクリーンショット ==

1. ブロックエディタでのアプリ検索画面
2. フロントエンドでのアプリ情報表示

== 更新履歴 ==

= 1.2.0 =
* 管理画面・エディタUIの日本語対応
* テキストドメインを統一（app-link-generator）
* wp_set_script_translations() による翻訳ファイル読み込みに対応
* load_plugin_textdomain() を追加
* readme.txt を日本語化

= 1.1.0 =
* バッジ画像をローカル化（外部依存を解消）
* 外部サービス利用に関するドキュメントを追加
* キャッシュ機能の改善
* バグ修正およびパフォーマンス改善

= 1.0.0 =
* 初回リリース
* App StoreおよびGoogle Play Store対応
* ブロックエディタ統合
* 自動キャッシュおよび毎日自動更新

== アップグレードのご案内 ==

= 1.1.0 =
バッジ画像の外部依存を解消し、WordPress.orgプラグインガイドラインに準拠した外部サービス利用のドキュメントを追加しました。
