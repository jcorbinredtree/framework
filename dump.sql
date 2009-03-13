DROP TABLE IF EXISTS cms_page;
DROP TABLE IF EXISTS cms_page_data;
DROP TABLE IF EXISTS cms_page_node;
DROP TABLE IF EXISTS cms_node;
DROP TABLE IF EXISTS cms_node_data;

CREATE TABLE cms_page (
    cms_page_id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    created DATETIME NOT NULL,
    modified TIMESTAMP NOT NULL,
    path VARCHAR(255) NOT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'text/html',
    UNIQUE KEY(path),
    INDEX(created),
    INDEX(modified)
);
CREATE TABLE cms_page_data(
    cms_page_id INTEGER UNSIGNED NOT NULL,
    `key` VARCHAR(255) NOT NULL,
    type ENUM ('string', 'json'),
    value TEXT NOT NULL,
    PRIMARY KEY (cms_page_id, `key`),
    INDEX(cms_page_id),
    INDEX(`key`)
);
CREATE TABLE cms_page_node(
    cms_page_id INTEGER UNSIGNED NOT NULL,
    cms_node_id INTEGER UNSIGNED NOT NULL,
    area VARCHAR(255) NOT NULL,
    weight TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (cms_page_id, cms_node_id, area),
    INDEX (cms_page_id),
    INDEX (cms_node_id),
    INDEX (cms_page_id, area),
    INDEX (cms_page_id, area, weight)
);

CREATE TABLE cms_node (
    cms_node_id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    created DATETIME NOT NULL,
    modified TIMESTAMP NOT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'content',
    res_name VARCHAR(255),
    content TEXT,
    UNIQUE KEY(res_name),
    INDEX(created),
    INDEX(modified)
);
CREATE TABLE cms_node_data(
    cms_node_id INTEGER UNSIGNED NOT NULL,
    `key` VARCHAR(255) NOT NULL,
    type ENUM ('string', 'json'),
    value TEXT NOT NULL,
    PRIMARY KEY (cms_node_id, `key`),
    INDEX(cms_node_id),
    INDEX(`key`)
);

INSERT INTO `cms_page` VALUES
    (1,'2009-02-27 20:47:20','2009-02-28 04:45:07','foo.html','text/html');

INSERT INTO `cms_page_data` VALUES
    (1,'title','string','Foo'),
    (1,'description','string','Page Description yada yada'),
    (1,'keywords','json','kw1, kw2, kw3'),
    (1,'scripts','json','[{\"href\":\"some.js\"}]'),
    (1,'stylesheets','json','[{\"href\": \"some.css\"}, {\"href\": \"another.css\", \"alt\": true, \"title\":\"Alt Title\"}]'),
    (1,'links','json','[{\"href\":\"some.rss\", \"type\":\"application/rss+xml\", \"title\":\"RSS Feed\", \"rel\":\"alternate\"}]');

INSERT INTO `cms_node` VALUES
    (1, NOW(), NOW(), 'content', NULL, '<template><h3>Content Node 1</h3></template>'),
    (2, NOW(), NOW(), 'content', NULL, '<template><h3>Content Node 2</h3></template>'),
    (3, NOW(), NOW(), 'content', NULL, '<template><h3>Content Node 3</h3></template>'),
    (4, NOW(), NOW(), 'content', NULL, '<template><ul><li>alpha</li><li>beta</li><li>gamma</li></ul></template>');

INSERT INTO `cms_page_node` VALUES
    (1, 2, 'content', 0),
    (1, 3, 'content', 1),
    (1, 1, 'content', 2),
    (1, 4, 'left', 0);

-- vim:set ft=mysql sw=4 ts=4 expandtab:
