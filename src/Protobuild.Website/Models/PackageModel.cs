namespace Protobuild.Website.Models
{
    public class PackageModel
    {
        public const string Kind = "package";

        public const string TypeLibrary = "library";

        public const string TypeTemplate = "template";

        public const string TypeGlobalTool = "global-tool";

        public long Key { get; set; }

        public string GoogleId { get; set; }

        public string Name { get; set; }

        public string GitUrl { get; set; }

        public string Description { get; set; }

        public string Type { get; set; }

        public string DefaultBranch { get; set; }

        public string GetUrl(UserModel owner, string path = null)
        {
            if (string.IsNullOrWhiteSpace(path))
            {
                return owner.GetUrl(Name);
            }
            else
            {
                return owner.GetUrl(Name + "/" + path);
            }
        }

        public object ToJsonObject(UserModel owner)
        {
            return new
            {
                ownerID = GoogleId,
                name = Name,
                type = Type,
                moduleUrl = ProtobuildEnv.GetDomain() + GetUrl(owner),
                apiUrl = ProtobuildEnv.MakeApiUrl(ProtobuildEnv.GetDomain() + GetUrl(owner)),
                gitUrl = GitUrl,
                description = Description,
                defaultBranch = DefaultBranch
            };
        }
    }
}
