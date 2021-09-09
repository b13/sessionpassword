TYPO3 Extension Session Password
================================

[Latest Version on Packagist][link-packagist]
[Software License](LICENSE.txt)

Use a simple password form to add usergroups and show pages/content only for the current frontend user session.

On an active logout, the sessions are removed again.

## Installation

Just use `composer req b13/sessionpassword` and install the extension via the Extension Manager,
then you have a new plugin ready to go.


## Configuration

Depending on your use-case, the extension has one option in the Extension Settings
to use hashed passwords in the flexform (= stored in the database). It is useful to activate this option
especially for new installations, but with this option activated, there is no way for editors / admins to retrieve
the entered password again.


## Credits

* [Benni Mack][link-author]

## License

As this is a PHP project, extending TYPO3, all code is licensed as GPL v2+.

## Sharing our expertise

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure long-term performance, reliability, and results in all our code.

[link-author]: https://github.com/bmack
[link-packagist]: https://packagist.org/packages/b13/sessionpassword
