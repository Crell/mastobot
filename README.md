# Mastobot

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

Mastobot is a simple scheduled-post bot for Mastodon.  It is intended for single-user situations, where someone wants to self-host Mastodon automation.  It is intended to run via a cron task.

Common use cases include posting daily quotes from a set collection, or a slowly serialized (but automated) story.

## Installation

The easiest way to install Mastobot and keep it up to date is to clone the Git repository, and then check out the latest tag.

Then run `composer install` to install the necessary dependencies.

Finally, set up cron (or the scheduling tool of your choice) to run the following command fairly frequently:

```
php run.php
```

Mastobot will only post messages when cron runs, so, for example, if the bot runs hourly then all posts will happen on the hour.  If you'd prefer to have a random-seeming schedule, use a prime number of minutes (say, every 13 or 17 minutes).

## Configuration

Mastobot is controlled via a `mastobot.yaml` file in project root.  The available keys are below.

### `app_name` (required)

The name of your bot.  Should be a short, semi-unique human-readable name.  "Mastobot" is a reasonable default.

### `accounts` (required)

This is a named dictionary of account connection information.  There are four properties, all required.  The name of each account definition will be used by the `posters` list below, but is otherwise an arbitrary string.

#### `app_instance` (required)

The Mastodon instance to which the bot will post.  It should be just the domain name, no `http` or anything.  So `mastodon.social`, `phpc.social`, etc.

#### Authentication information (required)

There are three identifiers you need from your profile to authenticate any Mastodon bot.  Mastobot does not include a mechanism to auto-generate those for you, by design, but they're simple to generate from the UI.

In your Settings page, select the "Development" tab.  Click the "New Application" button.  Provide a name for the application that is specific to you.

You may leave the rest of the settings at their default.  Or, if you'd rather lock down the bot's access, `write:statuses` and `write:media` are the only required permissions.

Click "Save Changes" when you're done.

The next page will show you three hash values: "Client Key", "Client Secret", and "Your access token".  Those are the three values we need.  Copy those values into `mastobot.yaml` as `client_key`, `client_secret`, and `token`, respectively.

### `defaults`

This optional array property lets you set default configuration values for each post.  They may be overriden per-post (see below).

There are three properties that are meaningful to set here.

* `visibility` - The visibility of each post.  Legal values are `public`, `unlisted`, `private` (aka followers-only), and `direct`.  Direct is almost never useful.  The default is `unlisted`, which means the post is public but will not show up in the local timeline of your instance.  Check your instance's policies for bots to determine if they have any specific requirements.
* `language` - An ISO 639 language code to specify what language the post is in, such as `en`, `de`, or `fr`.
* `spoiler_text` - Also known as a "content warning".

### `state_file`

Mastobot will store application state after each run in a JSON file on disk.  The default is to use a file named `mastobot_state.json` in the project root.  If your project root is not writeable (which is often a good for security), you may also specify a relative or absolute path to any other file name, as long as the file is writeable.

In most cases you can skip this field.

### `posters`

This is an array property that defines the auto-poster services that will run.  You may have any number of posters (although 0 is rather pointless).  Each poster uses a different `strategy`, which may have its own configuration.

At this time there are two available `strategy`s: `random` and `sequence`.

* `random` - This will select one status message at random from a directory each time it posts.  There is no "memory" from one run to the next; the value is re-randomized every time.
* `sequence` - This will post status messages from a directory in lexical order.  Once a post is made, it will not be posted again.

Both strategies have three required configuration parameters:

* `account` - The name of the credentials from the `accounts` section that this poster should use.  Defining multiple accounts allows a single Mastobot instance to power an arbitrary number of auto-posting accounts.
* `directory` - The directory on disk where posts will be drawn from.  If a relative path, it will be evaluated relative to the project root.  It may also be an absolute path pointing to anywhere you wish.  The `directory` MUST be unique among all listed `posters`.
* `minHours` - Two consecutive posts will be *at least* this many hours apart.
* `maxHours` - Two consecutive posts will be *at most* this many hours apart, as of when Mastobot next runs.

Whenever a post is made, Mastobot will generate a random timestamp between `minHours` and `maxHours` in the future and record that value.  The next time the bot runs, it will check if the current time is now past that timestamp.  If not, it wil do nothing.  If so, it will post the next status message and re-record the next-timestamp.

## Example configuration

For clarity, here's an example of a possible (likely) configuration:

```yaml
app_name: "Mastobot"
accounts:
  crell:
    app_instance: "phpc.social"
    client_id: "xxxx"
    client_secret: "yyyy"
    token: "zzzz"

defaults:
  language: "en"

posters:
  quotes:
    strategy: "random"
    account: "crell"
    directory: "posts/quotes"
    minHours: 20
    maxHours: 30
  story:
    strategy: "random"
    account: "crell"
    directory: "/home/me/posts/story"
    minHours: 5
    maxHours: 6
```

In this example, a random message in the `posts/quotes` directory will be posted every 20-30 hours (that is, the gap between posts will be between 72,000 and 108,000 seconds).  Additionally, posts from the `/home/me/posts/story` directory will be posted in lexical order, with a gap of between 18,000 and 21,600 seconds between them.  Both will be posted to the account on `phpc.social` defined by the `crell` account block.

## Post directories

Each strategy relies on a directory that contains status messages to post.  Mastobot supports six formats for posts, including both files and directories, all of which can be mixed-and-matched.  For the `sequence` strategy, files and directories are included together in the same lexical list.

### Simple messages

For most posts, a simple text message (a file ending in `.txt`) is sufficient.  The entire value of the text file will be included verbatim as the body of the post.

### JSON messages

Alternatively, a simple JSON message (a file ending in `.json`) allows specifying values beyond the message body.  A complete example with possible values is below:

```json
{
  "status": "The body of the status message.",
  "spoiler_text": "The spoiler text or content warning, if any",
  "language": "en",
  "visibility": "unlisted"
}
```
### YAML messages

A status may be defined using YAML as well.  The above example in YAML would be:

```yaml
status: "The body of the status message.",
spoiler_text: "The spoiler text or content warning, if any",
language: "en",
visibility: "unlisted"
```

### Directory definitions

A status may also be defined as a directory.  That allows for the inclusion of attached media, as well as metadata for that media.  At this time only images are supported, not audio or video.

If a directory contains a `status.txt`, `status.json`, or `status.yaml` file (checked in that order), it will be parsed the same way as a stand-alone file.  

Additionally, any `.gif`, `.png`, `.jpg`, `.jpeg`, or `.webp` files in the directory will be attached to the status message, in lexical order.

Optionally, you may also include a `json` or `yaml` file with a name matching the image file.  That file contains additional metadata for the image, such as alt-text.  The potential values are listed below.

```yaml
description: The alt text for this image, which you should always include.
# The position of the image that should be focused on when cropping.
focus:
  x: 0.2
  y: -0.2
```

You are strongly encouraged to always at least include a description, for accessibility.

## Why not use scheduled posts?

The ActivityPub and Mastodon API supports scheduled posts.  However, most Mastodon UIs do not include a way to review, modify, or cancel modified posts.  While it would be possible, and perhaps more efficient, for Mastobot to just push all posts at once with a scheduled time, that would allow no way to modify or clean up scheduled posts without building a complete UI for it as part of Mastobot.

That's out of scope for now, so instead it just posts normally.  However, you may use the `scheduled_at` property in a JSON post to cause the post to be scheduled instead of published immediately.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email larry at garfieldtech dot com instead of using the issue tracker.

## Credits

- [Larry Garfield][link-author]
- [All Contributors][link-contributors]

## License

The Affero GPL version 3 or later. Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/Crell/mastobot.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/License-AGPLv3-green.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/Crell/mastobot.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/Crell/mastobot
[link-scrutinizer]: https://scrutinizer-ci.com/g/Crell/mastobot/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/Crell/mastobot
[link-downloads]: https://packagist.org/packages/Crell/mastobot
[link-author]: https://github.com/Crell
[link-contributors]: ../../contributors
