namespace Protobuild.Website.Models
{
    public class BranchModel
    {
        public const string Kind = "branch";

        public long Key { get; set; }

        public string GoogleId { get; set; }

        public string PackageName { get; set; }

        public string BranchName { get; set; }

        public string VersionName { get; set; }

        public bool IsAutoBranch { get; set; }

        public object ToJsonObject()
        {
            return new
            {
                ownerID = GoogleId,
                packageName = PackageName,
                branchName = BranchName,
                versionName = VersionName
            };
        }
    }
}
