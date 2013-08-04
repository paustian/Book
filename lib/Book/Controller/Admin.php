<?php

// pnadmin.php,v 1.22 2007/03/15 17:32:44 paustian Exp
// ----------------------------------------------------------------------
// Original Author of file: Timothy Paustian
// Purpose of file:  Book administration display functions
// ----------------------------------------------------------------------
class Book_Controller_Admin extends Zikula_AbstractController {

    static public function url_replace_func($matches) {
        //you have to do two amp amp because the browser translates one of them.
        //first replace the amp
        $ret_text = 'href="' . preg_replace('|&([^a][^m][^p][^;])|', '&amp;amp;$1', $matches[1]) . '"';

        return $ret_text;
    }

    /**
     * book_admin_main
     * Function called when there is no modifiers to the module
     * This basically only calls the menu
     */
    public function main() {
        //security check
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        $render = Zikula_View::getInstance('Book', false);
        $render->assign('title', $this->__('Book'));

        return $render->fetch('book_admin_menu.htm');
    }

    /**
     * add new book
     * Create a new book. This presents the form for giving a title to the book
     */
    public function newbook() {
        $render = Zikula_View::getInstance('Book', false);

        // Security check - important to do this as early as possible to avoid
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        return $render->fetch('book_admin_newbook.htm');
    }

    /**
     * add new chapter to a book
     */
    public function newchapter() {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $render = Zikula_View::getInstance('Book', false);


        $bookItems = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        if ($bookItems == 0) {
            //if we dont' have a book, then you
            //cannot have chapters
            return LogUtil::registerError($this->__("You have to create a book before you can create a chapter or an article"));
        }
        //I now need to convert this to a new array that can be used in
        //the call to FormSelectMultiple. I actually need to construct
        //an array of arrays
        $menu = array();
        foreach ($bookItems as $item) {
            // Security check
            if (SecurityUtil::checkPermission('Book::Chapter', $item['bid'] . "::.*", ACCESS_ADD)) {
                $menu[$item['bid']] = $item['name'];
            }
        }
        $render->assign('bookmenu', $menu);

        return $render->fetch('book_admin_newchapter.htm');
    }

    /**
     * add new article to a chapter
     * This is a standard function that is called whenever an administrator
     * wishes to create a new module item
     */
    public function newarticle() {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }


        //get the complete list of books
        $books = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        if ($books == false) {
            //if we dont' have a book, then you
            //cannot have chapters
            return LogUtil::registerError($this->__('You have to create a book before you can create a chapter or an article'));
        }

        $chap_menus = array();


        //get all the chapters for each book using the bids
        //we can get this from the $books array
        foreach ($books as $book_item) {
            $bid = $book_item['bid'];
            //grab all the chapters for this book
            $chap_info = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));
            //check to make sure they are legitimate. The function will
            //send back false if it fails
            if ($chap_info == false) {
                continue;
            }
            //Now create the menu.
            //Walk through each chapter in the book and add it.
            $menuItem = array();
            foreach ($chap_info as $chap_item) {
                // Security check
                if (SecurityUtil::checkPermission("Book::Chapter", $book_item['bid'] . "::" . $chap_item['cid'], ACCESS_ADD)) {
                    $menuItem[$chap_item['cid']] = $chap_item['name'];
                }
            }
            $chap_menus[] = $menuItem;
        }

        if (!count($chap_menus)) {
            return LogUtil::registerError($this->__('You have to create a Chapter before you can create an an article'));
        }


        // Create output object
        $render = Zikula_View::getInstance('Book', false);
        $render->assign('books', $books);
        $render->assign('chapters', $chap_menus);

//create the language list
        $render->assign('language', 'eng');


        // Return the output that has been generated by this function
        return $render->fetch('book_admin_newarticle.htm');
    }

    /**
     * newfigure
     * add new figure to a book
     *
     */
    public function newfigure() {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        // Create output object
        $render = Zikula_View::getInstance('Book', false);


        $books = ModUtil::apiFunc('Book', 'user', 'getall');
        if ($books == false) {
            return LogUtil::registerError($this->__('You have to create a book before you can create a chapter or an article'));
        }

        $bookMenu = array();
        foreach ($books as $book_item) {
            if (SecurityUtil::checkPermission('Book::Chapter', "$book_item[bid]::.*", ACCESS_ADD)) {
                $bookMenu[$book_item['bid']] = $book_item['name'];
            }
        }
        $render->assign('books', $bookMenu);
        //There is no data to grab to insert in this form.
        //Figures in this design are basically free and can
        //be added irrespective of books, chapters or articles
        return $render->fetch('book_admin_newfigure.htm');
    }

    /**
     * newglossary
     * Create a new glossary item for the book. We do not need to grab
     * any data associated with a book, because the glossary can be used
     * for any book.
     */
    public function newglossary() {
        // Create output object - this object will store all of our output so that
        // we can return it easily when required
        $render = Zikula_View::getInstance('Book', false);

        // Security check
        //If you have access to the module, you can add glossary items.
        if (!SecurityUtil::checkPermission('Book::', '.*::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // Return the output that has been generated by this function
        return $render->fetch('book_admin_newglossary.htm');
    }

    /**
     * Standard function to create a new book. Processes the results of the
     * form from book_admin_newbook()
     * @param name the name of the book
     */
    public function create($args) {
        // Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        // Get parameters from whatever input we need.
        $name = FormUtil::getPassedValue('name', isset($args['name']) ? $args['name'] : null);

        // Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'view'));
        }

        $tid = ModUtil::apiFunc('Book', 'admin', 'create', array('name' => $name));

        if ($tid != false) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('Book item created'));
        }

        pnRedirect(pnModurl('Book', 'admin', 'newbook'));

        return true;
    }

    /**
     * Processes the results of the form supplied by book_admin_newchapter()
     * to create a new item
     * @param 'name' the name of the chapter to be created
     * @param 'number' the number of the chatper to be created
     * @param 'book' the book to attach the chapter to
     */
    public function createchapter($args) {
        // Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        // Get parameters from whatever input we need.
        $name = FormUtil::getPassedValue('name', isset($args['name']) ? $args['name'] : null);
        $number = FormUtil::getPassedValue('number', isset($args['number']) ? $args['number'] : null);
        $book = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);

        // Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'view'));
        }

        // The API function is called.
        $cid = ModUtil::apiFunc('Book', 'admin', 'createchapter', array('name' => $name,
            'number' => $number,
            'bid' => $book));

        if ($cid != false) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('Book item created'));
        }

        pnRedirect(pnModurl('Book', 'admin', 'newchapter'));

        return true;
    }

    /**
     * Processes the results of the form supplied by book_admin_newarticle()
     * to create a new item
     * @param 'title' the title of the article to be created
     * @param 'content' the article content
     * @param 'next' the next article to link to, may be empty
     * @param 'prev' the previous article to link to, may be empty
     * @param 'chapter' the chapter id that the article belongs to
     * @param 'lang' the language of the article
     */
    public function createarticle($args) {
        // Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        //get the parameters
        $title = FormUtil::getPassedValue('title', isset($args['title']) ? $args['title'] : null);
        $contents = FormUtil::getPassedValue('contents', isset($args['contents']) ? $args['contents'] : null);
        $next = FormUtil::getPassedValue('next', isset($args['next']) ? $args['next'] : null);
        $prev = FormUtil::getPassedValue('prev', isset($args['prev']) ? $args['prev'] : null);
        $bid = FormUtil::getPassedValue('book', isset($args['book']) ? $args['book'] : null);
        $lang = FormUtil::getPassedValue('lang', isset($args['lang']) ? $args['lang'] : null);
        $aid = FormUtil::getPassedValue('aid', isset($args['aid']) ? $args['aid'] : null);

        $chapter_id = 'chapter_' . $bid;
        $chapter = FormUtil::getPassedValue($chapter_id, isset($args[$chapter_id]) ? $args[$chapter_id] : null);

        //set some variables since it doesn't make any sense for them to be empty
        if ($aid == "") {
            $aid = 999;
        }

        // Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'newarticle'));
        }
        // print "T:" . $title . "\nb_id:" . $bid . "\nA number:" .  $aid. "\nchapter:" . $chapter. "\nlang:" . $lang . "\ncontents:" . $contents;
        // die;
        // The API function is called.
        //It should return the article number ($number) as the id
        $article_id = ModUtil::apiFunc('Book', 'admin', 'createarticle', array('title' => $title,
            'bid' => $bid,
            'contents' => $contents,
            'next' => $next,
            'prev' => $prev,
            'aid' => $aid,
            'cid' => $chapter,
            'lang' => $lang));

        // The return value of the function is checked here, and if the function
        // suceeded then an appropriate message is posted.
        if ($article_id != false) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('An article was created'));
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'newarticle'));

        // Return
        return true;
    }

    /**
     * Processes the results of the form supplied by book_admin_newfigure()
     * to create a new item
     * @param 'fig_number' the number of the figure to be created
     * @param 'number' the number of the chatper the figure will be displayed in These two items will be used to identify figures
     * @param 'img_link' the path to the image to display
     * @param 'title' the title of the figure
     * @param 'content' the content of the figure. The legend
     */
    public function createfigure($args) {
        // Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        // Get parameters from whatever input we need.
        $fig_number = FormUtil::getPassedValue('fig_number', isset($args['fig_number']) ? $args['fig_number'] : null);
        $number = FormUtil::getPassedValue('number', isset($args['number']) ? $args['number'] : null);
        $img_link = FormUtil::getPassedValue('img_link', isset($args['img_link']) ? $args['img_link'] : null);
        $title = FormUtil::getPassedValue('title', isset($args['title']) ? $args['title'] : null);
        $content = FormUtil::getPassedValue('content', isset($args['content']) ? $args['content'] : null);
        $perm = FormUtil::getPassedValue('perm', isset($args['perm']) ? $args['perm'] : null);
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        if ($perm === 'on') {
            $perm = 1;
        } else {
            $perm = 0;
        }
        // Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'newfigure'));
        }

        // The API function is called.
        $fid = ModUtil::apiFunc('Book', 'admin', 'createfigure', array('fig_number' => $fig_number,
            'number' => $number,
            'img_link' => $img_link,
            'title' => $title,
            'perm' => $perm,
            'content' => $content,
            'bid' => $bid));

        if ($fid != false) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('Figure was created.'));
        }

        pnRedirect(pnModurl('Book', 'admin', 'newfigure'));

        return true;
    }

    /**
     * Processes the results of the form supplied by book_admin_newglossary()
     * to create a new item
     * @param 'term' the term to be defined
     * @param 'definition' the definition of the term
     */
    public function createglossary($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        // Get parameters from whatever input we need.
        $term = FormUtil::getPassedValue('term', isset($args['term']) ? $args['term'] : null);
        $definition = FormUtil::getPassedValue('definition', isset($args['definition']) ? $args['definition'] : null);


        // Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'newglossary'));
        }

        // The API function is called.
        $gid = ModUtil::apiFunc('Book', 'admin', 'createglossary', array('term' => $term,
            'definition' => $definition));

        if ($gid != false) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('Glossary item created'));
        }

        pnRedirect(pnModurl('Book', 'admin', 'newglossary'));

        return true;
    }

    /**
     * modify a book
     * This is a standard function that is called whenever an administrator
     * wishes to modify a current module item
     */
    public function modify() {
        // Create output object
        $render = Zikula_View::getInstance('Book', false);


        // The user API function is called
        $items = ModUtil::apiFunc('Book', 'user', 'getall');

        if ($items == false) {
            return LogUtil::registerError($this->__('There are no books to get'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Book::', ".*::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $render->assign('books', $items);


        // Return the output that has been generated by this function
        return $render->fetch('book_admin_modify.htm');
    }

    /**
     * This is a standard function that is called with the results of the
     * form supplied by book_admin_modify() to update a current item
     * @param 'bid' the id of the book to be updated
     * @param 'name' the name of the book to be updated
     */
    public function update($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        // Get parameters from whatever input we need.
        //This is the radio button that is active
        //it will be a number
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        //The bid corresponds to the title for the book.
        $name = FormUtil::getPassedValue($bid, isset($args[$bid]) ? $args[$bid] : null);

        // Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'newglossary'));
        }

        // Call apiupdate to do all the work
        if (ModUtil::apiFunc('Book', 'admin', 'update', array('bid' => $bid,
                    'name' => $name))) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('The book was updated.'));
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'modify'));

        // Return
        return true;
    }

    /**
     * modify a chapter
     *
     */
    public function modifychapter() {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $render = Zikula_View::getInstance('Book', false);

        // The user API function is called
        $bookData = ModUtil::apiFunc('Book', 'user', 'getall');

        if ($bookData == false) {
            return LogUtil::registerError($this->__('There are no books to get.'));
        }

        $i = $j = 0;
        $chapters = array();
        $books = array();

        //get all the chapters for each book using the bids
        //we can get this from the $books array
        foreach ($bookData as $book_item) {
            // Security check
            if (SecurityUtil::checkPermission('Book::Chapter', "$book_item[bid]::", ACCESS_EDIT)) {
                $bid = $book_item['bid'];
            } else {
                continue;
            }
            //grab all the chapters for this book
            $chap_info = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));
            //check to make sure they are legitimate. The function will
            //send back false if it fails
            $j++;
            if ($chap_info == false) {
                $i++;
            } else {
                foreach ($chap_info as $chap_item) {
                    $chapters[] = $chap_item;
                    $bookMenuString = "";
                    //tricky thing here. We are looping the $bookdata twice
                    //make sure you use the right variable.
                    foreach ($bookData as $book_number) {
                        if ($book_number['bid'] == $chap_item['bid']) {
                            $bookMenuString .= "<option value=\"" . $book_number['bid'] . "\" label=\"" . $book_number['name'] . "\" selected>" . $book_number['name'] . "</option>\n";
                        } else {
                            $bookMenuString .= "<option value=\"" . $book_number['bid'] . "\" label=\"" . $book_number['name'] . "\">" . $book_number['name'] . "</option>\n";
                        }
                    }
                    $books[] = $bookMenuString;
                }
            }
        }

        if ($j == $i) {
            return LogUtil::registerError($this->__('You have to create a book before you can create a chapter or an article'));
        }

        $render->assign('books', $books);
        $render->assign('chaps', $chapters);

        // Return the output that has been generated by this function
        return $render->fetch('book_admin_modifychapter.htm');
    }

    /**
     * updatechapter
     * Update information pertaining to a given chapter
     * @param title the title of the chapter
     * @param number the chapter number. This helps order the chapters in the book
     * @param bid the id of the book the chapter belongs to. It is possible to move chapters in books
     *
     */
    public function updatechapter($args) {
        // Get parameters from whatever input we need.
        //This is the radio button that is active
        //it will be a number
        $cid = FormUtil::getPassedValue('cid', isset($args['cid']) ? $args['cid'] : null);
        $title_id = 'title_' . $cid;
        $title = FormUtil::getPassedValue($title_id, isset($args[$title_id]) ? $args[$title_id] : null);

        $number_id = 'number_' . $cid;
        $number = FormUtil::getPassedValue($number_id, isset($args[$number_id]) ? $args[$number_id] : null);
        $book_look = 'bid_' . $cid;
        $bid = FormUtil::getPassedValue($book_look, isset($args[$book_look]) ? $args[$book_look] : null);

        // Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'modifychapter'));
        }

        if (!SecurityUtil::checkPermission('Book::Chapter', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Call apiupdate to do all the work
        if (ModUtil::apiFunc('Book', 'admin', 'updatechapter', array('bid' => $bid,
                    'name' => $title,
                    'number' => $number,
                    'cid' => $cid))) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('The book was updated'));
        } else {
            LogUtil::registerError($this->__('Update of chapter failed.'));
            return false;
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'modifychapter'));

        // Return
        return true;
    }

    /**
     * modify an article. This happens in two
     * phases. The person first picks an article and then they
     * get a second form that allows them to change the text
     *
     */
    public function modifyarticle1() {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $render = Zikula_View::getInstance('Book', false);
        $render->caching = false;

        $book_string = $this->_make_list();
        $render->assign('books', $book_string);

        return $render->fetch('book_admin_modifyarticle1.htm');
    }

    private function _make_list($isForm = true, $access_level = ACCESS_EDIT) {
        $books = ModUtil::apiFunc('Book', 'user', 'getall');

        $have_articles = false;
        $chapters = array();
        $book_string = "<ul id=\"treemenu2\" class=\"treeview\">";
        $i = 0;
        foreach ($books as $book_item) {
            // Security check -- if we do not have permission for this book
            //just skip to the next.
            if (!SecurityUtil::checkPermission('Book::Chapter', "$book_item[bid]::", $access_level)) {
                continue;
            }


            $book_chapters = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $book_item['bid']));
            //add the book name
            $book_string .= "\n<li>" . DataUtil::formatForDisplayHTML($book_item['name']) . "\n<ul rel=\"open\">\n";

            foreach ($book_chapters as $chap_item) {
                //Allow access on a book level. We will restrict chapters
                if (SecurityUtil::checkPermission('Book::Chapter', "$book_item[bid]::$chap_item[cid]", $access_level)) {

                    $articles = ModUtil::apiFunc('Book', 'user', 'getallarticles', array('cid' => $chap_item['cid'],
                        'bid' => $book_item['bid'],
                        'get_content' => false));

                    //add the chapter name
                    $book_string .= "\t<li>" . DataUtil::formatForDisplayHTML($chap_item['number']) . " " . DataUtil::formatForDisplayHTML($chap_item['name']) . "\n<ul>\n";
                    foreach ($articles as $art_item) {
                        if ($art_item['title'] === "") {
                            continue;
                        }
                        if ($isForm) {
                            $inputTag = "<input name=\"chosen_article\" type=\"radio\" value=\"" . $art_item['aid'] . "\"> ";
                        }
                        $art_url = pnModurl('Book', 'user', 'displayarticle', array('aid' => $art_item['aid']));
                        $book_string .= "\t\t<li>" . $inputTag . "<a href=\"$art_url\">" .
                                DataUtil::formatForDisplayHTML($art_item['aid']) . " " . DataUtil::formatForDisplayHTML($art_item['title']) . "</a></li>\n";
                        $have_articles = true;
                    }
                    $book_string .= "</ul><!--article close-->\n"; //close article
                }
                $book_string .= "</li><!--chapt linke close-->"; //close chapter
            }
            $book_string .= "</ul>\n</li><!--booklinkclose-->";
        }

        $book_string .= "</ul><!--tree close -->";
        if ($have_articles == false) {
            return LogUtil::registerError($this->__('There was no articles to edit.'));
        }
        return $book_string;
    }

    /**
     * modify an article. This happens in two
     * phases. The person first picks an article and then they
     * get a second form that allows them to change the text
     *
     */
    public function modifyarticle2() {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

// Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'modifyarticle1'));
        }


        $aid = FormUtil::getPassedValue('chosen_article', isset($args['chosen_article']) ? $args['chosen_article'] : null);

        // Create output object
        $render = Zikula_View::getInstance('Book', false);

        //first get the article
        $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $aid));

        if ($article == false) {
            return LogUtil::registerError($this->__('There was no articles to edit'));
        }
        //now get the book and chapter
        $book = ModUtil::apiFunc('Book', 'user', 'get', array('bid' => $article['bid']));


        $chapters = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('all_chapters' => true));
        //This solves a problem where entities for editing were getting translated.
        //This is not working!
        //This will no longer happen
        $contents = preg_replace_callback('/href="(.*?)"/', 'Book_Controller_Admin::url_replace_func', $article['contents']);
        //we need all of this
        $contents = DataUtil::formatForDisplay($contents);
        $title = DataUtil::formatForDisplay($article['title']);
        $render->assign('contents', $contents);
        $render->assign('title', $title);
        $render->assign('next', $article['next']);
        $render->assign('prev', $article['prev']);
        $render->assign('aid', $article['aid']);
        $render->assign('bid', $article['bid']);
        $render->assign('aid', $article['aid']);

        //we only need the book name
        $render->assign('book', $book['name']);
        //build the chapter menu
        //note that we let the user assign the article to any chapter
        //not just the ones in this book.
        $chap_menu = array();

        foreach ($chapters as $chap_item) {
            // Security check
            $chap_menu[$chap_item['cid']] = $chap_item['name'];
        }
        $render->assign('chap_menu', $chap_menu);
        $render->assign('selected_chapter', $article['cid']);
        $render->assign('language', 'English');
        if (ModUtil::available('scribite')) {
            $scribite = pnModFunc('scribite', 'user', 'loader', array('areas' => array('content')));
            PageUtil::AddVar('rawtext', $scribite);
        }

        return $render->fetch('book_admin_modifyarticle2.htm');
    }

    /**
     * updatearticle
     * The function takes updated information for an article and adds it to the database
     * @param	$chapter_id
     * @param	$title
     * @param	$content
     * @param	$lang
     * @param	$next
     * @param	$prev
     *
     */
    public function updatearticle($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        // Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'modifyarticle1'));
        }
        // Get parameters from whatever input we need.
        //This is the radio button that is active
        //it will be a number
        $aid = FormUtil::getPassedValue('aid', isset($args['aid']) ? $args['aid'] : null);
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        $cid = FormUtil::getPassedValue('chapter_id', isset($args['chapter_id']) ? $args['chapter_id'] : null);
        $content = FormUtil::getPassedValue('contents', isset($args['contents']) ? $args['contents'] : null);
        $title = FormUtil::getPassedValue('title', isset($args['title']) ? $args['title'] : null);
        $lang = FormUtil::getPassedValue('lang', isset($args['lang']) ? $args['lang'] : null);
        $next = FormUtil::getPassedValue('next', isset($args['next']) ? $args['next'] : null);
        $prev = FormUtil::getPassedValue('prev', isset($args['prev']) ? $args['prev'] : null);
        $aid = FormUtil::getPassedValue('aid', isset($args['aid']) ? $args['aid'] : null);

        // Call apiupdate to do all the work
        if (ModUtil::apiFunc('Book', 'admin', 'updatearticle', array('aid' => $aid, 'bid' => $bid,
                    'title' => $title,
                    'contents' => $content,
                    'cid' => $cid,
                    'lang' => $lang,
                    'next' => $next,
                    'prev' => $prev,
                    'aid' => $aid))) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('Article updated.'));
        } else {
            LogUtil::registerError($this->__('Update of article failed.'));
            return false;
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'modifyarticle1'));
        return true;
    }

    public function modifyfigure1() {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $render = Zikula_View::getInstance('Book', false);

        // The user API function is called
        $figData = ModUtil::apiFunc('Book', 'user', 'getallfigures');


        if ($figData == false) {
            return LogUtil::registerError($this->__('There are no figures to edit'));
        }

        //make a menu of the last fifty figures
        $numFigures = count($figData);
        $fig_menu = array();
        //get all the chapters for each book using the bids
        //we can get this from the $books array

        for ($i = $numFigures - 1; $i > $numFigures - 50; $i--) {
            if ($i < 0)
                break;
            $fig_item = $figData[$i];
            $fig_menu[$fig_item['fid']] = $fig_item['title'];
        }

        $render->assign('fig_list', $fig_menu);

        //make the menu of books
        $bookItems = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        $book_menu = array();
        if ($bookItems != 0) {
            foreach ($bookItems as $item) {
                // Security check
                if (SecurityUtil::checkPermission('Book::Chapter', "$item[bid]::", ACCESS_EDIT)) {
                    $book_menu[$item['bid']] = $item['name'];
                }
            }
        }

        $render->assign('book_menu', $book_menu);

        // Return the output that has been generated by this function
        return $render->fetch('book_admin_modifyfigure1.htm');
    }

    public function modifyfigure2() {

        // Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'modifyfigure1'));
        }
        //grab parameters
        $fid = FormUtil::getPassedValue('fid2', isset($args['fid2']) ? $args['fid2'] : null);
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        $number = FormUtil::getPassedValue('number', isset($args['number']) ? $args['number'] : null);
        $fig_number = FormUtil::getPassedValue('fig_number', isset($args['fig_number']) ? $args['fig_number'] : null);


        if ($fid == "") {
            $fid = FormUtil::getPassedValue('fid1', isset($args['fid1']) ? $args['fid1'] : null);
            ;
        }


        // Create output object
        $render = Zikula_View::getInstance('Book', false);
        $figure = false;

        if (isset($bid) && isset($number) && isset($fig_number)) {
            $figure = ModUtil::apiFunc('Book', 'user', 'getfigure', array('bid' => $bid,
                'number' => $number,
                'fig_number' => $fig_number));
        }
        //unsuccessful, try the figure id
        if (!$figure) {

            $figure = ModUtil::apiFunc('Book', 'user', 'getfigure', array('fid' => $fid));
        }

        if ($figure == false) {
            return LogUtil::registerError($this->__('There are no articles to edit.'));
        }

        //we need all of this
        $render->assign('content', $figure['content']);
        $render->assign('title', $figure['title']);
        $render->assign('fig_number', $figure['fig_number']);
        $render->assign('img_link', $figure['img_link']);
        $render->assign('fid', $figure['fid']);
        $render->assign('perm', $figure['perm']);
        $render->assign('number', $figure['number']);
        $render->assign('bid', $figure['bid']);

        $books = ModUtil::apiFunc('Book', 'user', 'getall');
        if ($books == false) {
            return LogUtil::registerError($this->__('You have to create a book before you can create a chapter or an article'));
        }

        $bookMenu = array();
        foreach ($books as $book_item) {
            if (SecurityUtil::checkPermission('Book::Chapter', "$book_item[bid]::", ACCESS_EDIT)) {
                $bookMenu[$book_item['bid']] = $book_item['name'];
            }
        }
        $render->assign('books', $bookMenu);

        if (ModUtil::available('scribite')) {
            $scribite = pnModFunc('scribite', 'user', 'loader', array('areas' => array('content')));
            PageUtil::AddVar('rawtext', $scribite);
        }
        return $render->fetch('book_admin_modifyfigure2.htm');
    }

    /**
     * updatearticle
     * The function takes updated information for an article and adds it to the database
     * @param	$chapter_id
     * @param	$title
     * @param	$content
     * @param	$lang
     * @param	$next
     * @param	$prev
     *
     */
    public function updatefigure($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
// Confirm authorisation code.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'modifyfigure1'));
        }
        // Get parameters from whatever input we need.
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null);
        $fig_number = FormUtil::getPassedValue('fig_number', isset($args['fig_number']) ? $args['fig_number'] : null);
        $title = FormUtil::getPassedValue('title', isset($args['title']) ? $args['title'] : null);
        $img_link = FormUtil::getPassedValue('img_link', isset($args['img_link']) ? $args['img_link'] : null);
        $content = FormUtil::getPassedValue('content', isset($args['content']) ? $args['content'] : null);
        $number = FormUtil::getPassedValue('number', isset($args['number']) ? $args['number'] : null);
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        $perm = FormUtil::getPassedValue('perm', isset($args['perm']) ? $args['perm'] : null);

        if ($perm === 'on') {
            $perm = 1;
        } else {
            $perm = 0;
        }

        $result = ModUtil::apiFunc('Book', 'admin', 'updatefigure', array('fid' => $fid,
            'fig_number' => $fig_number,
            'title' => $title,
            'content' => $content,
            'img_link' => $img_link,
            'number' => $number,
            'perm' => $perm,
            'bid' => $bid));
        // Call apiupdate to do all the work
        if ($result) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('The figure was updated.'));
        } else {
            LogUtil::registerError($this->__('Update of figure failed.'));
            return false;
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'modifyfigure1'));

        // Return
        return true;
    }

    /**
     * modifyglossary1
     * First interface for modifying a glossary item
     *
     */
    public function modifyglossary1() {
        // Create output object
        $render = Zikula_View::getInstance('Book', false);

        //grab all the books and see if we have permission to modify any of them
        $bookItems = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));
        $authorized = false;
        if ($bookItems != 0) {
            foreach ($bookItems as $item) {
                // Security check -- you must have access to at least one book
                if (SecurityUtil::checkPermission('Book::Chapter', "$item[bid]::", ACCESS_EDIT)) {
                    $authorized = true;
                }
            }
        } else {
            //no books, how can you have a glossary?
            return LogUtil::registerError(_('You cannot created a glossary when there are no books to edit.'));
        }
        if (!$authorized) {
            return LogUtil::registerPermissionError();
        }
        // The user API function is called
        $glossary_terms = ModUtil::apiFunc('Book', 'user', 'getallglossary');

        if ($glossary_terms == false) {
            return LogUtil::registerError($this->__('There are no glossary terms to edit.'));
        }

        $term1 = array();
        $term2 = array();
        $term3 = array();
        $term4 = array();
        $gloss_count = count($glossary_terms);
        $remainder = $gloss_count % 4;
        $gloss_count -= $remainder;
        for ($i = 0; $i < $gloss_count; $i+=4) {
            $term1[] = $glossary_terms[$i];
            $term2[] = $glossary_terms[$i + 1];
            $term3[] = $glossary_terms[$i + 2];
            $term4[] = $glossary_terms[$i + 3];
        }
        $last3 = array('term' => "", 'gid' => "");
        $last2 = array('term' => "", 'gid' => "");
        $last1 = array('term' => "", 'gid' => "");
        //$remainder will be the last 0, 1, 2, or 3
        switch ($remainder) {
            case 3:
                $last3 = $glossary_terms[$gloss_count + 2];
            case 2:
                $last2 = $glossary_terms[$gloss_count + 1];
            case 1:
                $last1 = $glossary_terms[$gloss_count + 0];
                break;
        }
        $render->assign('term1', $term1);
        $render->assign('term2', $term2);
        $render->assign('term3', $term3);
        $render->assign('term4', $term4);
        $render->assign('last1', $last1);
        $render->assign('last2', $last2);
        $render->assign('last3', $last3);

        // Return the output that has been generated by this function
        return $render->fetch('book_admin_modifyglossary1.htm');
    }

    /**
     * modifyglossary1
     * Second interface for modifying a glossary item
     *
     */
    public function modifyglossary2($args) {
        // Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'modifyglossary1'));
        }
        $gid = FormUtil::getPassedValue('gid', isset($args['gid']) ? $args['gid'] : null);


        // Create output object
        $render = Zikula_View::getInstance('Book', false);

        // The user API function is called
        $glossData = ModUtil::apiFunc('Book', 'user', 'getglossary', array('gid' => $gid));
        //print_r($glossData);die;
        $render->assign('glossary', $glossData);

        // Return the output that has been generated by this function
        return $render->fetch('book_admin_modifyglossary2.htm');
    }

    public function updateglossary($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'modifyfigure1'));
        }

        $gid = FormUtil::getPassedValue('gid', isset($args['gid']) ? $args['gid'] : null);
        $term = FormUtil::getPassedValue('term', isset($args['term']) ? $args['term'] : null);
        $definition = FormUtil::getPassedValue('definition', isset($args['definition']) ? $args['definition'] : null);

        // Call apiupdate to do all the work
        if (ModUtil::apiFunc('Book', 'admin', 'updateglossary', array('gid' => $gid,
                    'term' => $term,
                    'definition' => $definition))) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('The glossary has been updated.'));
        } else {
            LogUtil::registerError($this->__('Update of glossary failed.'));
            return false;
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'modifyglossary1'));

        // Return
        return true;
    }

    /**
     * dodelete book
     * Interface for reemoving a book, its chapters and articles.
     *
     */
    public function dodelete() {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }
        // Create output object - this object will store all of our output so that
        // we can return it easily when required
        $render = Zikula_View::getInstance('Book', false);
        $render->caching = false;

        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        $bookItems = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        if ($bookItems == 0) {
            //if we dont' have a book, then you
            //cannot delete it
            return LogUtil::registerError($this->__('There is not book to edit.'));
        }

        $render->assign('books', $bookItems);

        return $render->fetch('book_admin_dodelete.htm');
    }

    /**
     * delete
     * Actually delete the book. Note that this also deletes the chapters and the articles
     *
     * @param	$args[bid]	The id of the book to delete
     *
     */
    public function delete($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'dodelete'));
        }

        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);

        // The API function is called.
        $tid = ModUtil::apiFunc('Book', 'admin', 'delete', array('bid' => $bid));
        if ($tid != false) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('The book was deleted.'));
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'view'));

        // Return
        return true;
    }

    /**
     * dodelete chapter
     * Interfacte for removal of the specified chapter and all its articles
     */
    public function chapterdisplay($args) {
        // Create output object - this object will store all of our output so that
        // we can return it easily when required
        $render = Zikula_View::getInstance('Book', false);


        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        $chap_menus = $this->_generate_chapter_menu($render);
        //print_r($chap_menus);die;
        $render->assign('chapters', $chap_menus);

        return $render->fetch('book_admin_dochapterdelete.htm');
    }

    private function _generate_chapter_menu($render) {
        //get the complete list of books
        $books = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        if ($books == false) {
            //if we dont' have a book, then you
            //cannot have chapters
            return LogUtil::registerError($this->__('You have to create a book before you can create a chapter or an article'));
        }

        $chapters = array();
        //$i and $j are counters that verify that there is at least one chatper in
        //one book. If no chapters have been created, then after the loop
        //$j and $i will be equal. In that case, do not allow the funciton to
        //continue.
        $i = $j = 0;
        //get all the chapters for each book using the bids
        //we can get this from the $books array
        foreach ($books as $book_item) {
            $bid = $book_item['bid'];
            //grab all the chapters for this book
            $chap_info = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));
            //check to make sure they are legitimate. The function will
            //send back false if it fails
            $j++;
            if ($chap_info == false) {
                $i++;
            }
            //we store this information for use later in
            //making our form. Each array item matches a book.
            //I could probably just put this down in the bottom
            //and write the form out, but this is a bit cleaner.
            $chapters[] = $chap_info;
        }
        //there are no chapters
        if ($j == $i) {
            return LogUtil::registerError($this->__('There are no chapters.'));
        }

        // Start the table
        $i = 0;

        $render->assign('books', $books);

        $chap_menus = array();
        foreach ($books as $book_item) {
            $menuItem = array();
            foreach ($chapters[$i] as $chap_item) {
                $menuItem[$chap_item['cid']] = $chap_item['name'];
            }
            $i++;
            $chap_menus[] = $menuItem;
        }
        return $chap_menus;
    }

    public function do_export($args) {
        $ret_url = pnModurl('book', 'admin', 'main');
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError($ret_url);
        }
        // Create output object - this object will store all of our output so that
        // we can return it easily when required
        $render = Zikula_View::getInstance('Book', false);

        //get the complete list of books
        $books = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        if ($books == false) {
            //if we dont' have a book, then you
            //cannot have chapters
            return LogUtil::registerError($this->__('There are no books to export'), null, $ret_url);
        }

        $chapters = array();
        //$i and $j are counters that verify that there is at least one chatper in
        //one book. If no chapters have been created, then after the loop
        //$j and $i will be equal. In that case, do not allow the funciton to
        //continue.
        $i = $j = 0;
        //get all the chapters for each book using the bids
        //we can get this from the $books array
        foreach ($books as $book_item) {
            $bid = $book_item['bid'];

            //grab all the chapters for this book
            $chap_info = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));
            //check to make sure they are legitimate. The function will
            //send back false if it fails
            $j++;
            if ($chap_info == false) {
                $i++;
            }
            //we store this information for use later in
            //making our form. Each array item matches a book.
            //I could probably just put this down in the bottom
            //and write the form out, but this is a bit cleaner.
            $chapters[] = $chap_info;
        }
        //there are no chapters to delete
        if ($j == $i) {
            return LogUtil::registerError($this->__('There are no chapters to export.'), null, $ret_url);
        }

        // Start the table
        $i = 0;

        $render->assign('books', $books);

        $chap_menus = array();
        foreach ($books as $book_item) {
            $menuItem = array();
            foreach ($chapters[$i] as $chap_item) {
                // Security check
                if (SecurityUtil::checkPermission('Book::Chapter', "$book_item[bid]::$chap_item[cid]", ACCESS_EDIT)) {
                    $menuItem[$chap_item['cid']] = $chap_item['name'];
                }
            }
            $i++;
            $chap_menus[] = $menuItem;
        }

        $render->assign('chapters', $chap_menus);

        return $render->fetch('book_admin_doexport.htm');
    }

    /**
     * delete chatper
     * The function that actually deletes the chapter
     * @param	cid	The id of the chapter to delete
     *
     */
    public function deletechapter($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'dodelete'));
        }

        //A little hocus pocus. Each menu for the
        //chapters is identified by its book id
        //we identify the correct one to delete by checking the book id.
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        $chap_to_get = 'chapter_' . $bid;
        $cid = FormUtil::getPassedValue($chap_to_get, isset($args[$chap_to_get]) ? $args[$chap_to_get] : null);

        // The API function is called.
        $tid = ModUtil::apiFunc('Book', 'admin', 'deletechapter', array('cid' => $cid));
        if ($tid != false) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('Chapter(s) deleted'));
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'chapterdisplay'));

        // Return
        return true;
    }

    /**
     * Remove an article from a chapter. This accomplishes several things
     * It takes out an article, then stiches together the next and prev
     * links form the two articles in the chapter that were next and prevous to
     * this one.
     */
    public function dodeletearticle() {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }
        $render = Zikula_View::getInstance('Book', false);
        $render->caching = false;

        $book_string = $this->_make_list();
        $render->assign('books', $book_string);

        return $render->fetch('book_admin_doarticledelete.htm');
    }

    /**
     * dodeletearticle
     *
     * Given an article id, delete it.
     */
    public function deletearticle($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'dodeletearticle'));
        }

        $aid = FormUtil::getPassedValue('chosen_article', isset($args['chosen_article']) ? $args['chosen_article'] : null);

        // The API function is called.
        $tid = ModUtil::apiFunc('Book', 'admin', 'deletearticle', array('aid' => $aid));
        if ($tid != false) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('Article(s) deleted.'));
            //an article was deleted, let the hooks, know about it
            // item deleted, so notify hooks of the event
            $hook = new Zikula_ProcessHook('book.ui_hooks.articles.process_delete', $aid);
            $this->notifyHooks($hook);
        }
        
        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'dodeletearticle'));

        // Return
        return true;
    }

    /**
     * dodelete
     *
     * Present a form for the user to choose a figure to delete.
     */
    public function dodeletefigure() {
        // Create output object
        $render = Zikula_View::getInstance('Book', false);

        // The user API function is called
        $figData = ModUtil::apiFunc('Book', 'user', 'getallfigures');


        if ($figData == false) {
            pnRedirect(pnModurl('Book', 'admin', 'dodeletefigure'));
            return LogUtil::registerError($this->__('There are no figures to delete.'));
        }


        // Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        //make a menu of the last fifty figures
        $numFigures = count($figData);
        $fig_menu = array();
        //get all the chapters for each book using the bids
        //we can get this from the $books array
        for ($i = $numFigures - 1; $i > $numFigures - 50; $i--) {
            if ($i < 0)
                break;
            $fig_item = $figData[$i];
            $fig_menu[$fig_item['fid']] = $fig_item['title'];
        }

        $render->assign('fig_list', $fig_menu);

        // Return the output that has been generated by this function
        return $render->fetch('book_admin_deletefigure.htm');
    }

    public function deletefigure($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'dodelete'));
        }
        //get the figure id.
        $fid = FormUtil::getPassedValue('fid2', isset($args['fid2']) ? $args['fid2'] : null);

        if ($fid == "") {
            $fid = FormUtil::getPassedValue('fid1', isset($args['fid1']) ? $args['fid1'] : null);
        }


        // Create output object
        $render = Zikula_View::getInstance('Book', false);

        if (ModUtil::apiFunc('Book', 'admin', 'deletefigure', array('fid' => $fid))) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('Book figure deleted.'));
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'dodeletefigure'));

        // Return
        return true;
    }

    public function dodeleteglossary() {
        // Create output object
        $render = Zikula_View::getInstance('Book', false);


        // The user API function is called
        $glossary_terms = ModUtil::apiFunc('Book', 'user', 'getallglossary');

        if ($glossary_terms == false) {
            return LogUtil::registerError($this->__('There are no glossary terms to delete.'));
        }
        
        // Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        //use the modulus operator in smarty and then break the row when the modulus hits 4
        //this is in the template, see this page.
        //http://www.smarty.net/docsv2/en/language.function.section.tpl
        $term1 = array();
        $term2 = array();
        $term3 = array();
        $term4 = array();
        $gloss_count = count($glossary_terms);
        $remainder = $gloss_count % 4;
        $gloss_count -= $remainder;
        for ($i = 0; $i < $gloss_count; $i+=4) {
            $term1[] = $glossary_terms[$i];
            $term2[] = $glossary_terms[$i + 1];
            $term3[] = $glossary_terms[$i + 2];
            $term4[] = $glossary_terms[$i + 3];
        }
        $last3 = array('term' => "", 'gid' => "");
        $last2 = array('term' => "", 'gid' => "");
        $last1 = array('term' => "", 'gid' => "");
        //$remainder will be the last 0, 1, 2, or 3
        switch ($remainder) {
            case 3:
                $last3 = $glossary_terms[$gloss_count + 2];
            case 2:
                $last2 = $glossary_terms[$gloss_count + 1];
            case 1:
                $last1 = $glossary_terms[$gloss_count + 0];
                break;
        }
        $render->assign('term1', $term1);
        $render->assign('term2', $term2);
        $render->assign('term3', $term3);
        $render->assign('term4', $term4);
        $render->assign('last1', $last1);
        $render->assign('last2', $last2);
        $render->assign('last3', $last3);

        // Return the output that has been generated by this function
        return $render->fetch('book_admin_dodeleteglossary.htm');
    }

    public function deleteglossary($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }
        //get the glossary id.
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'dodelete'));
        }
        //get the figure id.
        $gid = FormUtil::getPassedValue('gid', isset($args['gid']) ? $args['gid'] : null);
        $render = Zikula_View::getInstance('Book', false);

        if (ModUtil::apiFunc('Book', 'admin', 'deleteglossary', array('gid' => $gid))) {
            // Success
            SessionUtil::setVar('statusmsg', $this->__('Glossary item(s) deleted.'));
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        pnRedirect(pnModurl('Book', 'admin', 'dodeleteglossary'));

        // Return
        return true;
    }

    /**
     * addglossary
     *
     * @param $args['aid'] the article ID to change
     *
     * This is fired from a button diplayed on each page when in administration view. It will call
     * the api function that scans through the glossary and replaces each match with a definition.
     * This right now is not allowed to be changed for a whole book as it uses regular experessions
     * and each page should be viewed after modification to make sure its acceptible.
     *
     * @return true - redirects to the updated article.
     */
    public function addglossaryitems($args) {
        $render = Zikula_View::getInstance('Book', false);

        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }
        $aid = FormUtil::getPassedValue('aid', isset($args['aid']) ? $args['aid'] : null);
        //call the api function to do the work.
        ModUtil::apiFunc('Book', 'admin', 'addglossaryitems', array('aid' => $aid));
        //display the newly changed article
        pnRedirect(pnModurl('Book', 'user', 'displayarticle', array('aid' => $aid)));
        return true;
    }

    /**
     * view items
     */
    public function view() {
        //just return the main view.
        return $this->main();
    }

    /**
     * This is a standard function to modify the configuration parameters of the
     * module. Right now we don't have any conguration parameters
     */
    public function modifyconfig() {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $render = new pnRender();

        if (pnModGetVar('Book', 'securebooks')) {
            $render->assign('issecure', "checked");
        } else {
            $render->assign('issecure', '');
        }
        return $render->fetch('book_admin_modifyconfig.htm');
    }

    /**
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form
     */
    public function updateconfig() {
        return true;
    }

    /**
     * Main administration menu
     */
    public function menu() {
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        // Create output object - this object will store all of our output so that
        // we can return it easily when required
        $render = Zikula_View::getInstance('Book', false);
        // Start options menu
        $render->assign('title', $this->__('The Book Module'));

        // Return the output that has been generated by this function
        return $render->fetch('book_admin_menu.htm');
    }

    /**
     * modifyaccess
     *
     * Change the access to the book. If this is turned on, then only one person per username is allowed to
     * access the book at a time. This prevents people from cheating.
     */
    public function modifyaccess() {
        // Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $secure = FormUtil::getPassedValue('secure', isset($args['secure']) ? $args['secure'] : null);
        ModUtil::setVar('Book', 'securebooks', $secure == "makesecure");
        pnRedirect(pnModurl('Book', 'admin', 'modifyconfig'));
        return true;
    }

    /**
     * export
     *
     * book_admin_export
     *
     * Export a chapter in a format for editing. Right now this basically accumilates
     * the html and spits it out to be edited. As long as you don't mess with the
     * tags, it should import correctly.
     *
     * @params	$args['cid']	The id of the chapter to export.
     */
    public function exportchapter($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'view'));
        }

        //get the book id to process
        $bid = FormUtil::getPassedValue('book', isset($args['book']) ? $args['book'] : null);
        $inline_figures = FormUtil::getPassedValue('inline', isset($args['inline']) ? $args['inline'] : null);
        $chap_to_get = 'chapter_' . $bid;
        $cid = FormUtil::getPassedValue($chap_to_get, isset($args[$chap_to_get]) ? $args[$chap_to_get] : null);
        //The presented text is almost xhtml compliant. I need to change the inline
        //figures which are with curly braces and then strip the glossary entries and it's
        //all set. I should also add a funciton that adds the references to the figures if a switch is
        //set. This way I can use it for publishing the text, instead of having it go through the browser
        //which hoses it up. I also need to test this xhtml file through prince pdfs
        // Create output object
        //there are still problems with this. it may be better to
        //do a separate export calling the user function if you want the figures inlined. Try that.
        //
    //replace the glossary entries
        // $text = preg_replace('|<a class="glossary" href=.*?\'\)">(.*?)</a>|', '<a class="glossary">$1</a>' ,$text); 
        $text = "";
        if ($inline_figures == 'on') {
            //process the chapter adding links to the figures.
            $text = pnModFunc('Book', 'user', 'displaychapter', array('cid' => $cid));
            $text = preg_replace('|<a class=\"glossary\".*?\'\)\">(.*?)</a>|', '$1', $text);
        } else {

            $text = $this->exportchapter_noinline(array('cid' => $cid));
        }   
        //remove amersands in urls
        //Author: TImothy Paustian date: August 1 2010
        //This was a tricky problem. I finally settled on a double search function
        //we first pick out all the urls, cause there is there the problem is,
        //I don't use & in my text. Now that the & is isolated, I can then
        //do another call to preg_replace. The tricky part was that the browser was
        //reading the entity and fixing it, so I have to add a
        //second amp; to get it to read right out of the form.
        $text = preg_replace_callback('|href="(.*?)"|', "Book_Controller_Admin::url_replace_func", $text);
        
        $book = ModUtil::apiFunc('Book', 'user', 'get', array('bid' => $bid));
        
        $render = Zikula_View::getInstance('Book');
        $render->assign('export_text', $text);
        $render->assign('name', $book['name']);
        $text = $render->fetch('book_admin_export.htm');
        return $text;
    }

    public function exportchapter_noinline($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        //get the chapter id
        $cid = FormUtil::getPassedValue('cid', isset($args['cid']) ? $args['cid'] : null);

        if (!isset($cid)) {
            return LogUtil::registerError(_MODARGSERROR);
        }

        $chapter = ModUtil::apiFunc('Book', 'user', 'getchapter', array('cid' => $cid));


        $articles = ModUtil::apiFunc('Book', 'user', 'getallarticles', array('cid' => $cid,
            'get_content' => true));
        $render = Zikula_View::getInstance('Book', false);

        $render->assign('chapter', $chapter);
        $num_arts = count($articles);
        for ($i = 0; $i < $num_arts; $i++) {
            $articles[$i]['contents'] = preg_replace('/\&(.*?);/', '&amp;$1;', $articles[$i]['contents']);
        }
        $render->assign('articles', $articles);

        $return_text = $render->fetch('book_admin_chapter_xml.htm');
        //we need to clean out the glossary and Figure notation stuff.
        //it will be brought right back in on import
        //This stuff really still screws up the book.
        //I guess we are just going to have to leave it.

        /* $pattern = '/<a class="glossary" href.*?\'\)">(.*?)<\/a>/';
          $replacement = '<a class="glossary">$1</a>';
          $return_text = preg_replace($pattern, $replacement, $return_text);

          $pattern = '/<a.*?>Figure ([0-9]+)-([0-9]+)<\/a>/';
          $replacement = 'Figure $1-$2';
          $return_text = preg_replace($pattern, $replacement, $return_text); */
        $render->assign('export_text', $return_text);
        return $return_text;
    }

    /**
     * exportbook
     *
     * book_admin_exportbook
     *
     * Export a book in a format for editing. Right now this basically accumilates
     * the html and spits it out to be edited. As lon as you don't mess with the
     * tags, it should import correctly.
     *
     * @params	$args['bid']	The id of the book to export.
     */
    public function exportbook($args) {
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'view'));
        }

        $bid = FormUtil::getPassedValue('book', isset($args['book']) ? $args['book'] : null);

        if (!isset($bid)) {
            return LogUtil::registerError(_MODARGSERROR);
        }

        $book = ModUtil::apiFunc('Book', 'user', 'get', array('bid' => $bid));

        $chapters = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));


        $render = Zikula_View::getInstance('Book', false);
        $export_text = "";
        //now cycle through each chapter delimiting its boundries.
        foreach ($chapters as $chap_item) {
            //flank the return text with chapter xml
            $export_text = $export_text . "<br />" . pnModFunc('Book', 'admin', 'exportchapter', array('cid' => $chap_item['cid']));
        }

        $render->assign('export_text', $export_text);
        $render->assign('name', $book['name']);
        return $render->fetch('book_admin_export.htm');
    }

    /**
     * book_admin_doimport
     *
     * we just provide a text area for the modified chatper to
     * be added to.
     */
    public function doimport() {
        $render = Zikula_View::getInstance('Book', false);
        // Security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        return $render->fetch('book_admin_import.htm');
    }

    /**
     * book_admin_import
     *
     * Import a chapter into the textbook. This should take the exported text
     * and reprocess it. One problem I may need to solve is munged text.
     * I will have to do some serious checking for missing params, and if not
     * there, unwind the whole process.
     *
     * @params	$args['text']	The text to import.
     */
    public function import($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        //security check
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'view'));
        }

        $in_data = FormUtil::getPassedValue('chap_to_import', isset($args['chap_to_import']) ? $args['chap_to_import'] : null);
        if (!isset($in_data)) {
            return LogUtil::registerArgsError();
        }

        $matches = array();
        $pattern = '|<!--\(bookname\)(.*?)\(\/bookname\)-->|';
        preg_match($pattern, $in_data, $matches);
        $name = $matches[1];
        //grab bid
        $pattern = '|<!--\(bookid\)([0-9]*?)\(\/bookid\)-->|';
        preg_match($pattern, $in_data, $matches);
        $bid = $matches[1];
        //update the book data
        if (!ModUtil::apiFunc('Book', 'admin', 'update', array('bid' => $bid, 'name' => $name))) {
            //if update fails, try create
            if (!ModUtil::apiFunc('Book', 'admin', 'create', array('bid' => $bid, 'name' => $name))) {
                //set an error message and return false
                SessionUtil::setVar('error_msg', $this->__('Book import failed. Ha ha.'));
            }
        }
        $chap_matches = array();
        //now match each section<!
        $pattern = '|<!--\(chapter\)-->(.*)<!--\(/chapter\)-->|s';
        preg_match_all($pattern, $in_data, $chap_matches, PREG_PATTERN_ORDER);

        foreach ($chap_matches[1] as $chap_data) {

            //grab the title of the imported chapter
            $pattern = '|<!--\(chapname\)(.*?)\(\/chapname\)-->|';
            preg_match($pattern, $chap_data, $matches);
            $chap_title = $matches[1];

            //grab the chapter id
            $pattern = '|<!--\(chapid\)([0-9]*)\(\/chapid\)-->|';
            preg_match($pattern, $chap_data, $matches);
            $cid = $matches[1];
            //grab chapter number
            $pattern = '|<!--\(chapnumber\)([0-9]*)\(\/chapnumber\)-->|';
            preg_match($pattern, $chap_data, $matches);
            $number = $matches[1];
            $pattern = '|<!--\(bookid\)([0-9]*?)\(\/bookid\)-->|';
            preg_match($pattern, $chap_data, $matches);
            $bid = $matches[1];
            // Security check
            if (!SecurityUtil::checkPermission('Book::Chapter', "$book[bid]::$chapter[cid]", ACCESS_EDIT)) {
                return LogUtil::registerPermissionError();
            }
            //update the chapter data
            if (!ModUtil::apiFunc('Book', 'admin', 'updatechapter', array('bid' => $bid, 'name' => $chap_title, 'number' => $number, 'cid' => $cid))) {
                //if update fails, try create
                if (!ModUtil::apiFunc('Book', 'admin', 'createchapter', array('bid' => $bid, 'name' => $chap_title, 'number' => $number, 'cid' => $cid))) {
                    //set an error message and return false
                    SessionUtil::setVar('error_msg', $this->__('Chapter update failed. Ha ha.'));
                }
            }
            //debugging code do not remove
            //return "chap title $chap_title <br> chap id: $cid <br> chap number:$number
            //		<br>book id: $bid <br>" ;
            //now match each section
            $pattern = '|<!--\(section\)-->(.*?)<!--\(\/section\)-->|s';
            preg_match_all($pattern, $chap_data, $matches, PREG_PATTERN_ORDER);

            foreach ($matches[1] as $match_item) {
                //extract the data for each article
                //and then update it.
                //<p class="art_aid}1</p>
                $pattern = '|<!--\(artartid\)([0-9]*)\(\/artartid\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $aid = $art_match[1];

                //{art_counter}78{/p>
                $pattern = '|<!--\(artcounter\)([0-9]*)\(\/artcounter\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $art_counter = $art_match[1];

                //{art_lang}eng{/p>
                $pattern = '|<!--\(artlang\)(.*?)\(\/artlang\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $art_lang = $art_match[1];

                //{art_next}2{/p>
                $pattern = '|<!--\(artnext\)([0-9]*)\(\/artnext\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $art_next = $art_match[1];

                //{art_prev}0{/p>
                $pattern = '|<!--\(artprev\)([0-9]*)\(\/artprev\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $art_prev = $art_match[1];

                //{aid}1{/p>
                $pattern = '|<!--\(artnumber\)([0-9]*)\(\/artnumber\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $aid = $art_match[1];

                //{div class="contents}....{/div>
                $pattern = '|<!--\(content\)-->(.*?)<!--\(\/content\)-->|s';
                preg_match($pattern, $match_item, $art_match);
                $art_content = $art_match[1];

                //{h2 class="art_title} Microbes in the environment{/h2>
                $pattern = '|<!--\(arttitle\)(.*?)\(\/arttitle\)-->|';
                preg_match($pattern, $match_item, $art_match);
                $art_title = $art_match[1];
                //Note, check this out first by just printing it.
                //Now that we have all the data, update the article

                /* Debugging code, do not remove
                 * $ret_text .= "<p>aid: $aid<br>art_counter: $art_counter<br>art_lang:$art_lang<br>" .
                  "art_next:$art_next<br>art_prev:$art_prev<br>aid:$aid<br>" .
                  "art_title: $art_title<br>art_contents: $art_content<br>";
                 */

                if (!ModUtil::apiFunc('Book', 'admin', 'updatearticle', array('aid' => $aid,
                            'bid' => $bid,
                            'title' => $art_title,
                            'contents' => $art_content,
                            'cid' => $cid,
                            'lang' => $art_lang,
                            'next' => $art_next,
                            'prev' => $art_prev,
                            'aid' => $aid))) {
                    // failure
                    //try creating it then
                    if (!ModUtil::apiFunc('Book', 'admin', 'createarticle', array('aid' => $aid, 'bid' => $bid, 'title' => $art_title, 'content' => $art_content, 'cid' => $cid, 'lang' => $art_lang, 'next' => $art_next, 'prev' => $art_prev, 'aid' => $aid))) {
                        /* print "<p>aid: $aid<br>art_counter: $art_counter<br>art_lang:$art_lang<br>" .
                          "art_next:$art_next<br>art_prev:$art_prev<br>aid:$aid<br>" .
                          "art_title: $art_title<br>art_contents: $art_content<br>";die; */
                        $prev_error = pnSessionGetVar('error_msg');
                        SessionUtil::setVar('errormsg', $this->__('Book import failed.') . $prev_error);
                        return false;
                    }
                }
            }
        }
        //if we get here, we succeded
        SessionUtil::setVar('statusmsg', $this->__('Import succedeed'));

        //We now need to process the entire book, so send it along
        ModUtil::apiFunc('Book', 'admin', 'processalldocuments', array('bid' => $bid));

        pnRedirect(pnModurl('Book', 'admin', 'doimport'));

        // Return
        return true;
    }

    public function dolistbookfigures() {
        // Create output object - this object will store all of our output so that
        // we can return it easily when required
        $render = Zikula_View::getInstance('Book', false);
        $render->caching = false;

        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $bookItems = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        if ($bookItems == 0) {
            //if we dont' have a book, then you
            //cannot have chapters
            return LogUtil::registerError($this->__('You have no books so you cannot list the figures'));
        }
        $render->assign('books', $bookItems);

        return $render->fetch('book_admin_dolistfigure.htm');
    }

    public function listbookfigures($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'view'));
        }
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        $button = FormUtil::getPassedValue('submit');

        // Create output object
        $render = Zikula_View::getInstance('Book', false);
        $render->caching = false;

        // The user API function is called
        $figData = ModUtil::apiFunc('Book', 'user', 'getallfigures', array('bid' => $bid));

        if ($figData == false) {
            pnRedirect(pnModurl('Book', 'admin', 'dolistbookfigures'));
            return LogUtil::registerError($this->__('There are no figures to list'));
        }


        $render->assign('figData', $figData);
        $ret_text = "";
        if ($button == 'listpaths') {
            $ret_text = $render->fetch('book_admin_listpaths.htm');
        } else {
            $ret_text = $render->fetch('book_admin_listigures.htm');
        }
        // Return the output that has been generated by this function
        return $ret_text;
    }

    public function modifyimagepaths($args) {
        //only admins can do this
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError(pnModurl('Book', 'admin', 'view'));
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'view'));
        }

        $fids = FormUtil::getPassedValue('fid');
        $img_paths = FormUtil::getPassedValue('img_link');

        //we now have the figure paths and the ones we want to change. Walk through the list and change each one
        foreach ($fids as $fid) {
            $new_path = $img_paths[$fid];
            $figure = ModUtil::apiFunc('Book', 'user', 'getfigure', array('fid' => $fid));

            $result = ModUtil::apiFunc('Book', 'admin', 'updatefigure', array('fid' => $figure['fid'],
                'fig_number' => $figure['fig_number'],
                'title' => $figure['title'],
                'content' => $figure['content'],
                'img_link' => $new_path,
                'number' => $figure['number'],
                'perm' => $figure['perm'],
                'bid' => $figure['bid']));
            // Call apiupdate to do all the work
            if ($result) {
                // Success
                SessionUtil::setVar('statusmsg', $this->__('The figure was updated.'));
            } else {
                LogUtil::registerError($this->__('Update of figure failed.'));
                return false;
            }
        }

        return pnRedirect(pnModurl('Book', 'admin', 'dolistbookfigures'));
    }

    public function choose_verify_url() {
        $render = Zikula_View::getInstance('Book', false);

        //only admins can do this
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError(pnModurl('Book', 'admin', 'view'));
        }
        //get the complete list of books
        $books = ModUtil::apiFunc('Book', 'user', 'getall', array('startnum' => 1));

        if ($books == false) {
            //if we dont' have a book, then you
            //cannot have chapters
            return LogUtil::registerError($this->__('There are no urlS to check since you have not created a book.'));
        }

        $chapters = array();
        //$i and $j are counters that verify that there is at least one chatper in
        //one book. If no chapters have been created, then after the loop
        //$j and $i will be equal. In that case, do not allow the funciton to
        //continue.
        $i = $j = 0;
        //get all the chapters for each book using the bids
        //we can get this from the $books array
        foreach ($books as $book_item) {
            $bid = $book_item['bid'];
            //grab all the chapters for this book
            $chap_info = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));
            //check to make sure they are legitimate. The function will
            //send back false if it fails
            $j++;
            if ($chap_info == false) {
                $i++;
            }
            //we store this information for use later in
            //making our form. Each array item matches a book.
            //I could probably just put this down in the bottom
            //and write the form out, but this is a bit cleaner.
            $chapters[] = $chap_info;
        }
        //there are no chapters to delete
        if ($j == $i) {
            return LogUtil::registerError($this->__('There are no chapters, so there are no urlS to check.'));
        }

        // Start the table
        $i = 0;

        $render->assign('books', $books);

        $chap_menus = array();
        foreach ($books as $book_item) {
            $menuItem = array();
            foreach ($chapters[$i] as $chap_item) {
                $menuItem[$chap_item['cid']] = $chap_item['name'];
            }
            $i++;
            $chap_menus[] = $menuItem;
        }

        $render->assign('chapters', $chap_menus);

        return $render->fetch('book_admin_verify_chapter.htm');
    }

    public function verify_urls($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'dodelete'));
        }
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        $chap_to_get = 'chapter_' . $bid;
        $cid = FormUtil::getPassedValue($chap_to_get, isset($args[$chap_to_get]) ? $args[$chap_to_get] : null);

        $url_table = array();
        if (isset($cid)) {
            $chapter_info = ModUtil::apiFunc('Book', 'user', 'getchapter', array('cid' => $cid));
            $articles = ModUtil::apiFunc('Book', 'user', 'getallarticles', array('cid' => $cid));

            foreach ($articles as $article_item) {
                // Security check
                if (SecurityUtil::checkPermission('Book::', "$book[bid]::$chapter_info[cid]", ACCESS_EDIT)) {
                    buildtable($article_item['contents'], $url_table, $chapter_info['number'], $article_item['aid']);
                }
            }
        }

        $render = new pnRender('Book');
        $render->assign('url_table', $url_table);

        return $render->fetch('book_admin_verify_urls.htm');
    }

    function buildtable($content, &$url_table, $chap_no, $article_no) {
        $matches = array();
        $url_row = array();
        $new_urls = array();
        preg_match_all("/<a.*?href=\"(.*?)\"/", $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $url_item) {
            $url_row['url'] = $url_item[1];
            $url_row['chap_no'] = $chap_no;
            $url_row['article_no'] = $article_no;
            $new_urls[] = $url_row;
        }
        $new_urls = checkurls($new_urls);
        $url_table = array_merge($url_table, $new_urls);
    }

//include 'get_headers.php';

    /*
     * checkurls
     * parameters
     * 	$args=>cid	the chapter id
     *  $args=>bid	the book id
     *
     * Given a book id or chapter id, work through the articles in the book and find each
     * url. Then check them all. When the checking is done, a report lists all the bad urls
     * and their article location. Note, this does not list internal links, since those return
     * without error due to the way zikula is set up.
     */

    function checkurls($urls) {
        //the url to the current server
        $baseurl = pnGetBaseurl();
        $i = 0;
        foreach ($urls as $items) {
            //check to see if it is a valid url
            if (!is_url($items['url'])) {
                if (preg_match("/^\\//", $items['url'])) {
                    //root directory. Append the host and stop
                    //remove the first /
                    $items['url'] = pnServerGetProtocol() . "://" . pnServerGetHost() . $items['url'];
                } else {
                    //relative link
                    $items['url'] = $baseurl . trim($items['url'], "/");
                }
            }
            //this is an internal link
            if (strpos(strtolower($items['url']), strtolower($baseurl)) !== FALSE) {
                //check it internally
                //first parse it.
                $url_array = parse_url($items['url']);
                $arr_query = array();
                $args = explode('&', $url_array['query']);
                foreach ($args as $arg) {
                    $parts = explode('=', $arg);
                    $arr_query[$parts[0]] = $parts[1];
                }
                $modname = $arr_query['module'];
                if ($arr_query['type'] != "") {
                    $type = $arr_query['type'];
                } else {
                    $type = 'user';
                }
                if ($arr_query['func'] != "") {
                    $func = $arr_query['func'];
                } else {
                    $func = 'main';
                }
                $modfunc = "{$modname}_{$type}_{$func}";
                if (pnModLoad($modname, $type) && function_exists($modfunc)) {
                    $urls[$i]['present'] = 1;
                } else {
                    $urls[$i]['present'] = -1;
                }
            } else {
                $urls[$i]['present'] = check_http_link($items);
            }
            $i++;
        }

        return $urls;
    }

    function is_url($url) {
        if (!preg_match('/^http\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+/i', $url)) {
            return false;
        } else {
            return true;
        }
    }

    function check_http_link($inItem) {
        if (!is_valid_url($inItem['url'])) {
            return -1;
        } else {
            return 1;
        }
    }

    function is_valid_url($url) {
        $url = @parse_url($url);

        if (!$url) {
            return false;
        }

        $url = array_map('trim', $url);
        $url['port'] = (!isset($url['port'])) ? 80 : (int) $url['port'];
        $path = (isset($url['path'])) ? $url['path'] : '';

        if ($path == '') {
            $path = '/';
        }

        $path .= ( isset($url['query']) ) ? "?$url[query]" : '';

        if (isset($url['host']) AND $url['host'] != gethostbyname($url['host'])) {
            if (PHP_VERSION >= 5) {
                $headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
            } else {
                $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

                if (!$fp) {
                    return false;
                }
                fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
                $headers = fread($fp, 128);
                fclose($fp);
            }
            $headers = ( is_array($headers) ) ? implode("\n", $headers) : $headers;
            return (bool) preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
        }
        return false;
    }

    /*
     * checkstudentdefs
     *
     * Students can request words to be defined. These will appear as words with empty definitions.
     * This routine will find all empty definitions in the glossary and then display them to the author.
     * The author can then define them.
     *
     *
     */

    public function checkstudentdefs() {
        //security check
        //only admins can do this

        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            LogUtil::registerPermissionError(pnModurl('Book', 'admin', 'view'));
            return true;
        }

        $empty_defs = ModUtil::apiFunc('Book', 'admin', 'getemptyglossaryitems');
        //put all the gids into an array, we use this for grabbing them later
        $gids = array();
        foreach ($empty_defs as $def_item) {
            $gids[] = $def_item['gid'];
        }
        $render = Zikula_View::getInstance('Book', false);

        $render->assign('gids', DataUtil::formatForDisplayHTML(serialize($gids)));
        $render->assign('empty_defs', $empty_defs);

        return $render->fetch('book_admin_checkstudent_defs.htm');
    }

    public function modifyglossaryitems($args) {
        //only admins can do this
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            LogUtil::registerPermissionError(pnModurl('Book', 'admin', 'view'));
            return true;
        }
        //grab the array listing all the gloss ids to be updated
        $gloss_array = FormUtil::getPassedValue('gids', isset($args['gids']) ? $args['gids'] : null);
        $gids = unserialize($gloss_array);

        //each item in the table is defined by a combination
        //of what it is and the gloss id, farm each one out using
        //the array we just grabbed from the form.
        foreach ($gids as $gloss_item) {
            $term = FormUtil::getPassedValue('term_' . $gloss_item, isset($args['term_' . $gloss_item]) ? $args['term_' . $gloss_item] : null);
            $definition = FormUtil::getPassedValue('definition_' . $gloss_item, isset($args['definition_' . $gloss_item]) ? $args['definition_' . $gloss_item] : null);
            $delete = FormUtil::getPassedValue('delete_' . $gloss_item, isset($args['delete_' . $gloss_item]) ? $args['delete_' . $gloss_item] : null);
            //first check if we are supposed to delete it
            if ($delete == "on") {
                if (!ModUtil::apiFunc('Book', 'admin', 'deleteglossary', array('gid' => $gloss_item))) {
                    LogUtil::registerError($this->__('Glossary deletion failed.'), null, pnModurl('Book', 'admin', 'checkstudentdefs'));
                    return false;
                }
            } else {

                //we don't want to delete, we want to modify
                if (!ModUtil::apiFunc('Book', 'admin', 'updateglossary', array('gid' => $gloss_item, 'term' => $term, 'definition' => $definition))) {
                    LogUtil::registerError($this->__('Glossary modification failed.'), null, pnModurl('Book', 'admin', 'checkstudentdefs'));
                    return false;
                }
            }
        }

        //if we get here we were successful,
        SessionUtil::setVar('statusmsg', $this->__('Book glossary updated.'));
        pnRedirect(pnModurl('Book', 'admin', 'checkstudentdefs'));
        return true;
    }

    public function importglossaryitems($args) {
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError(pnModurl('Book', 'admin', 'view'));
        }
        $render = Zikula_View::getInstance('Book', false);

        return $render->fetch('book_admin_importglossaryitems.htm');
    }

    public function doglossaryimport($args) {

        //only admins can do this
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError(pnModurl('Book', 'admin', 'view'));
        }
        $gloss_data = FormUtil::getPassedValue('gloss_text', isset($args['gloss_text']) ? $args['gloss_text'] : null);
        //The glossary text is set up as a xml file
        //<glossitem>
        //<term>term</term>
        //<definition>defnintion</definition>
        //</glossitem>
        //parse this out into an array
        $matches=array();
        $pattern = '/<glossitem>(.*?)<\/glossitem>/s';
        preg_match_all($pattern, $gloss_data, $matches, PREG_PATTERN_ORDER);
        //now that we have them all, walk through each one and grab the term and definition
        foreach ($matches[1] as $match_item) {
            //grab the term
            $pattern = '/<term>(.*?)<\/term>/';
            preg_match($pattern, $match_item, $matches);
            $term = $matches[1];
            //grab the defintion
            $pattern = '/<definition>(.*?)<\/definition>/';
            preg_match($pattern, $match_item, $matches);
            $def = $matches[1];
            //update the glossary with the item
            ModUtil::apiFunc('Book', 'admin', 'createglossary', array('term' => $term, 'definition' => $def));
        }
        pnRedirect(pnModurl('Book', 'admin', 'view'));
        return true;
    }

    /**
     * book_admin_dosearchreplace1
     * Set up for the search replace feature of the module. The function diplsays
     * a form to the user for entrance of a search string, replace string, chooses
     * a book or chapter, and then whether to search through figures.
     * @param $args
     * @return unknown_type
     */
    public function dosearchreplace1() {
        //you have to have edit permission to do this
        if (!SecurityUtil::checkPermission('Book::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError(pnModurl('Book', 'admin', 'view'));
        }
        // Create output object - this object will store all of our output so that
        // we can return it easily when required
        $render = Zikula_View::getInstance('Book', false);

        $chap_menus = $this->_generate_chapter_menu($render);
        $render->assign('chapters', $chap_menus);

        return $render->fetch('book_admin_dosearchreplace1.htm');
    }

    public function dosearchreplace2($args) {
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(pnModurl('Book', 'admin', 'dosearchreplace2'));
        }

//grab the serach and replace information
        $bid = FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null);
        $search_pat = FormUtil::getPassedValue('search_pat', isset($args['search_pat']) ? $args['search_pat'] : null);
        $replace_pat = FormUtil::getPassedValue('replace_pat', isset($args['replace_pat']) ? $args['replace_pat'] : null);
        $preview = FormUtil::getPassedValue('preview', isset($args['preview']) ? $args['preview'] : null);
        $chap_to_get = 'chapter_' . $bid;
        $cid = FormUtil::getPassedValue($chap_to_get, isset($args[$chap_to_get]) ? $args[$chap_to_get] : null);
        $preview_text = "";
        if ($cid == 0) {
            //do the whole book
            $preview_text = ModUtil::apiFunc('Book', 'admin', 'dosearchreplacebook', array('bid' => $bid,
                'search_pat' => $search_pat,
                'replace_pat' => $replace_pat,
                'preview' => $preview === 'on'));
        } else {
            $preview_text = ModUtil::apiFunc('Book', 'admin', 'dosearchreplacechap', array('bid' => $bid,
                'cid' => $cid,
                'search_pat' => $search_pat,
                'replace_pat' => $replace_pat,
                'preview' => $preview === 'on'));
        }

        if ($preview === 'on') {
            $render = Zikula_View::getInstance('Book', false);
            $render->assign('preview_text', $preview_text);
            $render->assign('search_pat', $search_pat);
            $render->assign('replace_pat', $replace_pat);
            $render->assign('cid', $cid);
            $chap_menus = $this->_generate_chapter_menu($render);
            $render->assign('chapters', $chap_menus);
            return $render->fetch('book_admin_dosearchreplace1.htm');
        }

        return pnRedirect(pnModurl('Book', 'admin', 'dosearchreplace1'));
    }
}

?>