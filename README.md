# ER図
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
    
