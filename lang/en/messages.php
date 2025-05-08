<?php

return [
    // ***************************
    // *** 1xxxx プロフィール関連 ***
    // ***************************
    10001 => 'ProfileNicknameDuplicated',                 // ニックネームが重複している
    10002 => 'ProfileUserIdRequired',                     // ユーザIDが指定されていない
    10003 => 'ProfileNicknameRequired',                   // ニックネームが指定されていない
    10004 => 'ProfileTitleRequired',                      // タイトルが指定されていない
    10005 => 'ProfileConfigTypeUndefined',                // コンフィグタイプが定義されていない

    // ****************************
    // *** 2xxxx 投稿関連        ***
    // ****************************
    20001 => 'PostDateRequired',                        // 投稿日が指定されていない
    20002 => 'PostTitleRequired',                       // 記事タイトルが指定されていない 

    // ****************************
    // *** 4xxxx 場所関連        ***
    // ****************************
    40001 => 'LocationTitleRequired',                   // 場所タイトルが指定されていない 

    // ****************************
    // *** 5xxxx アイテム関連     ***
    // ****************************
    50001 => 'ItemTitleRequired',                       // アイテムタイトルが指定されていない 

    // ****************************
    // *** 71xxx カテゴリ関連     ***
    // ****************************
    71001 => 'CategorySwapProfileMissmatch',            // カテゴリ入替において対象カテゴリのカテゴリ所有プロフィールが異なる
    71002 => 'CategorySwapTypeMissmatch',               // カテゴリ入替において対象カテゴリのカテゴリタイプが異なる
    71003 => 'CategoryParentProfileMissmatch',          // カテゴリと親カテゴリでカテゴリ所有プロフィールが異なる
    71004 => 'CategoryParentTypeMissmatch',             // カテゴリと親カテゴリでカテゴリタイプが異なる
    71005 => 'CategoryDeleteHasChild',                  // カテゴリ削除において子カテゴリが存在する
    71006 => 'CategoryContentProfileMissmatch',         // カテゴリ所有プロフィールとコンテンツ所有プロフィールが異なる
    71007 => 'CategoryContentTypeMissmatch',            // カテゴリタイプとコンテンツ種別が異なる
    71008 => 'CategoryProfileRequired',                 // カテゴリ所有プロフィールが指定されていない
    71009 => 'CategoryTypeRequired',                    // カテゴリタイプが指定されていない
    71010 => 'CategoryNameRequired',                    // カテゴリ名が指定されていない
    71011 => 'CategoryNameDuplicated',                  // カテゴリ所有プロフィールとカテゴリタイプの中でカテゴリ名が重複している

    // ****************************
    // *** 72xxx タグ関連        ***
    // ****************************
    72001 => 'TagProfileRequired',                      // タグ所有プロフィールが指定されていない
    72002 => 'TagTypeRequired',                         // タグタイプが指定されていない
    72003 => 'TagNameRequired',                         // タグ名が指定されていない
    72004 => 'TagNameDuplicated',                       // タグ所有プロフィールとタグタイプの中でタグ名が重複している
    72005 => 'TagContentProfileMissmatch',              // タグ所有プロフィールとコンテンツ所有プロフィールが異なる
    72006 => 'TagContentTypeMissmatch',                 // タグタイプとコンテンツ種別が異なる

    // ****************************
    // *** 73xxx レコード関連     ***
    // ****************************
    73001 => 'RecordRecorderProfileRequired',           // レコーダ所有プロフィールが指定されていない
    73002 => 'RecordRecorderTypeRequired',              // レコーダタイプが指定されていない
];
