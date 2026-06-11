# Repository Structure

This file gives a quick overview of how the project is organized.

## Top Level

```text
IT202-2026/
|-- Dockerfile
|-- README.md
|-- structure.md
|-- lib/
|-- partials/
|-- public_html/
`-- sql/
```

- `Dockerfile`: Container setup for running the project in a consistent environment.
- `README.md`: Student-facing repository overview with space to fill in course, module, and project details.
- `structure.md`: This file; a quick guide to the repo layout.
- `lib/`: Shared PHP code such as configuration and database connection helpers.
- `partials/`: Reusable page fragments such as headers, footers, or shared UI sections.
- `public_html/`: Web-accessible files for the site/app.
- `sql/`: SQL scripts, schema notes, or database-related files.

## lib/

```text
lib/
|-- .env
|-- config.php
|-- db.php
`-- README.md
```

- `.env`: Local environment settings, including `DB_URL`. This should stay out of source control.
- `config.php`: Loads and parses database configuration values.
- `db.php`: Creates and returns the PDO database connection.
- `README.md`: Notes about the purpose of the `lib` folder.

## partials/

```text
partials/
`-- README.md
```

- `partials/`: Intended for reusable PHP/HTML pieces that can be included across pages.

## public_html/

```text
public_html/
|-- index.php
|-- proposal.md
|-- README.md
|-- test_db.php
|-- m01/
|-- m02/
|-- m03/
|-- m04/
|-- m05/
|-- m06/
|-- m07/
|-- m08/
|-- m09/
|-- m10/
`-- project/
```

- `index.php`: Main web entry point.
- `proposal.md`: Proposal or planning notes for the course/project.
- `README.md`: Notes specific to the public web folder.
- `test_db.php`: Simple database connectivity test page.
- `m01/` to `m10/`: Module folders for organizing work by course module.
- `project/`: Folder for larger final-project work.

Each module folder currently contains a `.gitkeep` file so Git will track the folder even when it is empty.

## sql/

```text
sql/
`-- README.md
```

- `sql/`: Intended for database setup scripts, seed data, or query examples.
