using Protobuild.Website.Models;
using System.Collections.Generic;

namespace Protobuild.Website.ViewModels
{
    public class PackageViewModel
    {
        public UserModel User { get; set; }

        public PackageModel Package { get; set; }

        public List<VersionModel> Versions { get; set; }

        public List<BranchModel> Branches { get; set; }

        public bool JustUploaded { get; set; }

        public bool JustCreatedBranch { get; set; }

        public bool ViewerCanEditPackage { get; set; }
    }
}
