## General

- All documentation for Tempest can be found in vendor/tempest/framework/docs
- Use `php tempest make:` commands to create framework-specific classes. These are interactive commands that will guide you through setup

## QA

- When your work is done, run a series of QA tools by running `composer qa`.
- You can also run individual QA commands if a specific one fails:
    - `composer lint` to lint code
    - `composer fmt` to reformat code
    - `composer analyse` for static analysis
    - `composer test` for testing

## Frontend

- Use TailwindCSS
- Use Tempest view components where it makes sense