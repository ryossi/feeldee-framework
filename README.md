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
