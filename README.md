PDO wrapper for Kohana 3.2
-------------

I really hated the built in database module so i created my own.


Usage
-------------

    $stmt = DB::instance()->prepare("SELECT * FROM articles WHERE id = :article_id");
    $stmt->execute(array(':article_id' => $id));
    $data = $stmt->fetch();

    // Fetch method is PDO::FETCH_OBJ by default so you would do something like this to get the title
    $data->title
