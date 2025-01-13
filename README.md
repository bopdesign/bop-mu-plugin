# bop-mu-plugin
A helper plugin for actions and filters to modify WP functionality, and enhance security.

Use the `/includes` directory to declare any theme functionality. All files in this directory are imported inside of `bop-mu-plugin.php`.

## Directories

The `/includes` directory is organized into files based on the functionality/purpose of the code. These files can be modified as needed, but the following structure is recommended:

```text
includes/
└─── admin/         (admin setup, hooks and filters.)
└─── blocks/        (hooks and functions that override blocks and Gutenberg behavior)
└─── core/          (core plugin functionality)
└─── overrides/     (hooks and functions that override the behavior of WP Core)
└─── security/      (theme security hardening hooks)
```