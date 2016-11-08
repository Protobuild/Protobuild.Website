namespace Protobuild.Website.Exceptions
{
    public static class CommonErrors
    {
        public const string PAGE_NOT_FOUND = "The requested page was not found.";

        public const string USER_NOT_FOUND = "User not found.";
        public const string PACKAGE_NOT_FOUND = "Package not found.";
        public const string BRANCH_NOT_FOUND = "Branch not found.";
        public const string VERSION_NOT_FOUND = "Version not found.";

        public const string ACCESS_DENIED = "You don't have permission to perform that operation.";

        public const string USER_IS_NOT_ORGANISATION = "The specified user is not an organisation.";

        public const string PACKAGE_HAS_NO_VERSIONS = "Package has no available versions.";
        public const string PACKAGE_STILL_HAS_BRANCHES_OR_VERSIONS = "Package still has branches or versions.";

        public const string PACKAGE_BRANCHES_MANAGED_BY_GIT =
            "Package branches are obtained from the source URL, so you can't configure branches for this package.";

        public const string VERSION_ALREADY_HAS_FILE = "This version already has a file uploaded.";

        public const string MISSING_INFORMATION = "The request is missing information.";

        public const string NOT_AN_API = "This route does not support being called through the API.";
    }
}
