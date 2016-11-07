using System.Collections.Generic;

namespace Protobuild.Website.Models
{
    public class HomeModel
    {
        public List<HomeInstallerModel> Installers { get; set; }

        public string DetectedPlatform { get; set; }
    }
}
