 <?php
require 'vendor/autoload.php';

$mustache = new \Slim\Extras\Views\Mustache();

\Slim\Extras\Views\Mustache::$mustacheDirectory = 'vendor/mustache/mustache/src/Mustache/';
// During instantiation
$app = new \Slim\Slim(array(
	'mode' => 'development',
	'debug' => true,
	'templates.path' => './templates',
	'view' => $mustache
));

ORM::configure('sqlite:./blog.db');

$db = ORM::get_db();
$db->exec("
    CREATE TABLE IF NOT EXISTS post (
    	id TEXT, 
        title TEXT, 
        post TEXT
    );"
);

$app->get('/', function () use ($app) {
    $app->view()->appendData(array('name' => 'World'));

    $app->render('hello.mustache');
});

$app->get('/hello/:name', function ($name) use ($app) {
    $app->view()->appendData(array('name' => $name));

    $app->render('hello.mustache');
});

$app->post('/insertPost', function () use ($app) {
 $req = $app->request();

 $title = $req->post('title');
 $post = $req->post('post');
 $id = md5 ("$title $post");

 $new_post = ORM::for_table('post')->create();
 
 $new_post->id = $id;
 $new_post->title = $title;
 $new_post->post = $post;
 
 $new_post->save();

 $app->redirect("/post/$id");
});

$app->get('/posts', function () use ($app) {
 $app->view()->appendData(array('posts' => ORM::for_table('post')->find_many()));

 $app->render('posts.mustache');
});

$app->get('/post/:id', function ($id) use ($app) {
 
 $post = ORM::for_table('post')
    ->where_equal('id', $id)
    ->find_one();

 $app->view()->appendData(
 		array('title' => $post->title, 'post' => $post->post)
 	);

 $app->render('post.mustache');
});

$app->get('/addPost', function () use ($app) {
 $app->render('addPost.mustache');
});

$app->run();

