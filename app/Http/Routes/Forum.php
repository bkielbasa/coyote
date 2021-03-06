<?php

Route::group(['namespace' => 'Forum', 'prefix' => 'Forum', 'as' => 'forum.'], function () {
    // strona glowna forum
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    Route::post('Preview', ['uses' => 'HomeController@preview', 'as' => 'preview']);
    Route::get('Search', ['uses' => 'SearchController@index', 'as' => 'search']);

    Route::get('Tag/{tag}', ['uses' => 'HomeController@tag', 'as' => 'tag']);
    Route::post('Tag/save', ['uses' => 'TagController@save', 'as' => 'tag.save']);
    Route::get('Tag/Prompt', ['uses' => 'TagController@prompt', 'as' => 'tag.prompt']);
    Route::get('Tag/Validate', ['uses' => 'TagController@valid', 'as' => 'tag.validate']);
    Route::get('All', ['uses' => 'HomeController@all', 'as' => 'all']);
    Route::get('Unanswered', ['uses' => 'HomeController@unanswered', 'as' => 'unanswered']);
    Route::get('Mine', ['uses' => 'HomeController@mine', 'as' => 'mine', 'middleware' => 'auth']);
    Route::get('Subscribes', ['uses' => 'HomeController@subscribes', 'as' => 'subscribes']);
    Route::get('User/{id}', ['uses' => 'HomeController@user', 'as' => 'user']);
    Route::post('Mark', ['uses' => 'HomeController@mark', 'as' => 'mark']);

    // dodawanie zalacznika do posta
    Route::post('Upload', ['uses' => 'AttachmentController@upload', 'as' => 'upload']);
    // sciaganie zalacznika
    Route::get('Download/{id}', ['uses' => 'AttachmentController@download', 'as' => 'download']);
    // wklejanie zdjec przy pomocy Ctrl+V w textarea
    Route::post('Paste', ['uses' => 'AttachmentController@paste', 'as' => 'paste']);

    // formularz dodawania nowego watku na forum
    Route::get('{forum}/Submit/{topic?}', ['uses' => 'SubmitController@index', 'as' => 'topic.submit', 'middleware' => ['forum.access', 'forum.write']]);
    Route::post('{forum}/Submit/{topic?}', ['uses' => 'SubmitController@save', 'middleware' => ['forum.access', 'forum.write', 'post.response']]);
    // dodawanie lub edycja posta na forum
    Route::get('{forum}/Submit/{topic}/{post?}', ['uses' => 'SubmitController@index', 'as' => 'post.submit', 'middleware' => ['topic.access', 'forum.access', 'forum.write']]);
    Route::post('{forum}/Submit/{topic}/{post?}', ['uses' => 'SubmitController@save', 'middleware' => ['topic.access', 'forum.access', 'forum.write', 'post.response']]);
    Route::get('{forum}/{topic}/Edit/{post}', ['uses' => 'SubmitController@edit', 'as' => 'post.edit', 'middleware' => ['topic.access', 'forum.access', 'forum.write']]);
    // szybka zmiana tytulu watku
    Route::post('Topic/Subject/{topic}', ['uses' => 'SubmitController@subject', 'as' => 'topic.subject', 'middleware' => 'auth']);

    Route::post('{forum}/Mark', ['uses' => 'CategoryController@mark', 'as' => 'category.mark', 'middleware' => 'forum.access']);
    Route::post('{forum}/Section', ['uses' => 'CategoryController@section', 'as' => 'section']);

    // obserwowanie danego watku na forum
    Route::post('Topic/Subscribe/{topic}', ['uses' => 'TopicController@subscribe', 'as' => 'topic.subscribe', 'middleware' => 'auth']);
    // blokowanie watku
    Route::post('Topic/Lock/{topic}', ['uses' => 'LockController@index', 'as' => 'lock', 'middleware' => 'auth']);
    // podpowiadanie nazwy uzytkownika (w kontekscie danego watku)
    Route::get('Topic/Prompt/{id}', ['uses' => 'TopicController@prompt', 'as' => 'prompt']);
    // przeniesienie watku do innej kategorii
    Route::post('Topic/Move/{topic}', ['uses' => 'MoveController@index', 'as' => 'move']);
    // oznacz watek jako przeczytany
    Route::post('Topic/Mark/{topic}', ['uses' => 'TopicController@mark', 'as' => 'topic.mark']);

    // dziennik zdarzen dla watku
    Route::get('Stream/{topic}', ['uses' => 'StreamController@index', 'as' => 'stream', 'middleware' => ['auth']]);

    // widok kategorii forum
    Route::get('{forum}', ['uses' => 'CategoryController@index', 'as' => 'category', 'middleware' => 'forum.access']);
    // widok wyswietlania watku. {topic}
    Route::get('{forum}/{topic}-{slug}', ['uses' => 'TopicController@index', 'as' => 'topic', 'middleware' => ['forum.access', 'topic.access', 'topic.scroll', 'page.hit']]);

    // usuwanie posta
    Route::post('Post/Delete/{id}', ['uses' => 'DeleteController@index', 'as' => 'post.delete', 'middleware' => 'auth']);
    // obserwowanie posta
    Route::post('Post/Subscribe/{post}', ['uses' => 'PostController@subscribe', 'as' => 'post.subscribe', 'middleware' => 'auth']);
    // glosowanie na dany post
    Route::post('Post/Vote/{post}', ['uses' => 'VoteController@index', 'as' => 'post.vote']);
    // akceptowanie danego posta jako poprawna odpowiedz w watku
    Route::post('Post/Accept/{post}', ['uses' => 'AcceptController@index', 'as' => 'post.accept']);
    // historia edycji danego posta
    Route::get('Post/Log/{post}', ['uses' => 'LogController@log', 'as' => 'post.log']);
    // przywrocenie poprzedniej wersji posta
    Route::post('Post/Rollback/{post}/{id}', ['uses' => 'LogController@rollback', 'as' => 'post.rollback']);
    // mergowanie posta z poprzednim
    Route::post('Post/Merge/{post}', ['uses' => 'MergeController@index', 'as' => 'post.merge']);

    // edycja/publikacja komentarza oraz jego usuniecie
    Route::post('Comment/{id?}', ['uses' => 'CommentController@save', 'as' => 'comment.save', 'middleware' => ['auth', 'comment.access']]);
    Route::get('Comment/{id}', ['uses' => 'CommentController@edit', 'middleware' => ['auth', 'comment.access']]);
    Route::post('Comment/Delete/{id}', ['uses' => 'CommentController@delete', 'as' => 'comment.delete', 'middleware' => ['auth', 'comment.access']]);

    // glosowanie w ankiecie
    Route::post('{forum}/Poll/{id}', ['uses' => 'PollController@vote', 'as' => 'poll.vote', 'middleware' => ['auth', 'forum.access']]);

    // skrocony link do posta
    Route::get('{id}', ['uses' => 'ShareController@index', 'as' => 'share']);
});
