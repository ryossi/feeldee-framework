# Feeldee Framework

feeldee-frameworkは、日記、フィールドノート、趣味や活動の記録に特化したCMSを構築するためのLaravelパッケージです。

## ER図
```mermaid
erDiagram
    profiles {
        int id PK
        int user_id FK "ユーザID"
        string nickname UK "ニックネーム"
        string image "イメージ"
        string title "タイトル"
        string subtitle "サブタイトル"
        string introduction "紹介文"
        string home "ホーム" 
        boolean show_members "メンバーリスト表示"
    }
    configs {
        int id PK
        int profile_id FK "プロフィールID"
        string type "タイプ"
        json value "値"
    }
    profiles ||--o{ configs : configs
    categories {
        int id PK
        int profile_id FK "プロフィールID"
        string type "タイプ"
        string name "カテゴリー名"
        int parent_id "親カテゴリー"
        int order_number "表示順"
    }
    profiles ||--o{ categories : categories
    categories o|--|| categories : childs
    tags {
        int id PK
        int profile_id FK "プロフィールID"
        string type "タイプ"
        string name "タグ名"
        int order_number "表示順"
    }
    profiles ||--o{ tags : tags
    taggables {
        int tag_id FK "タグID"
        int taggable_id FK "タグ付け対象ID"
    }
    tags ||--o{ taggables : tags
    posts {
        int id PK
        int profile_id FK "プロフィールID"
        dateTime post_date "投稿日"
        string title "タイトル"
        string value "内容"
        string text "テキスト"
        string thumbnail "サムネイル"
        int category_id FK "カテゴリーID"
        boolean is_public "公開フラグ"
        int public_level "公開レベル"
    }
    profiles ||--o{ posts : posts
    posts ||--o{ taggables : tags
    posts ||--o{ categories : categories
```

## 開発者

### 導入方法

1. `git clone ryossi/feeldee-framework`でパッケージをダウンロードします。 
2. `composer install`でPHPの依存パッケージをインストールします。

### テスト環境

通常のテストは、コマンドプロンプトで以下のコマンドを実行してください。

`./vendor/bin/phpunit --testsuite Feature`

### XDebug利用

1. `cp .env.example .env`で.envをコピーして設定をカスタマイズしてください。
2. `docker compose up -d`でテストコンテナを起動してください。
3. `docker exec -it feeldee-framework bash`でテストコンテナに入ります。
4. ソースコードの必要な部分にブレイクポイントを設定します。
5. テストコンテナのコマンドプロンプトで`./vendor/bin/phpunit --testsuite Feature`を実行してください。
6. 最後に`docker compose down`でテストコンテナを終了します。

## ライセンス

このプラグインは、[MIT licence.](https://opensource.org/licenses/MIT)のもとで公開されています。

## 参考

- テスト環境には、[Testbench](https://github.com/orchestral/testbench)を利用しています。
