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

        public string GitURL { get; set; }

        public string Description { get; set; }

        public string Type { get; set; }
    }
}
