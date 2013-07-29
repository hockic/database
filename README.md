PDO wrapper for Kohana 3.x
-------------

A clean, simple and lightweight PDO wrapper for Kohana 3.x


Plain PDO
-------------

    $stmt = DB::instance()->prepare("SELECT title FROM articles WHERE id = :article_id");
    $stmt->execute(array(':article_id' => $id));
    $data = $stmt->fetch();

    // Fetch method is PDO::FETCH_OBJ by default so you would do something like this to get the title
    echo $data->title

Shorhand prepared statement
-------------
    $data = DB::instance()
            ->handle("SELECT title FROM articles WHERE id = :article_id", array(':article_id' => $id))
            ->fetch();

    echo $data->title

Shorhand update
-------------

    /**
     * @see DB::update
     */
    DB::instance->update('articles', array('title' => 'New title', 'body' => 'New body'), array('id' => $id))

Shorhand delete
-------------

    /**
     * @see DB::delete
     */
    DB::instance->delete('articles', array('id' => $id))

Shorhand insert
-------------

    /**
     * @see DB::insert
     */
    DB::instance->insert('articles', array('title' => 'Article title', 'body' => 'Article body'))
