# com.walkerdaniels.tagactivitytracking
Tag Activity Tracking for  Contacts when a Tag Added or Tag Removed.

Track Activity by contact: who added/removed tag, what tag, when.

Works in  interface or via REST API integration.

On install two activity types are added for contacts.

Tag Added
Tag Removed

When a tag is added or removed from a contact, this extension writes a contact activity record.

This extension was developed to allow queries via REST API, to find only changes to tags on contacts by contact and date. For getting contact via REST API,  and add to query target_contact_id, field.  Its not exposed currently in API Explorer.

When Tag is updated via API the user associated with change is the users  whose API Key was used to for API access.

On uninstall of extension,  Activity Types are removed and  Contacts Activities of  Tag Added or Tag Removed added to activity records are removed.

Tested in versions 4.7, 4.6, 4.4.

