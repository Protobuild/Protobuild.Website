using Protobuild.Website.Models;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Protobuild.Website.ViewModels
{
    public class UserViewModel
    {
        public UserModel User { get; set; }

        public List<PackageModel> Packages { get; set; }

        public List<UserModel> Owners { get; set; }
    }
}
