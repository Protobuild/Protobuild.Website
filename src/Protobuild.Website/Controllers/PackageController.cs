using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Protobuild.Website.Controllers
{
    public class PackageController : Controller
    {
        [Route("/{user}/{package}")]
        public IActionResult Index(string user, string package)
        {
            return View();
        }

        [Route("/{user}/{package}/edit")]
        public IActionResult Edit(string user, string package)
        {
            return View();
        }

        [Route("/{user}/{package}/delete")]
        public IActionResult Delete(string user, string package)
        {
            return View();
        }

        [Route("/{user}/{package}/version/new")]
        public IActionResult VersionNew(string user, string package)
        {
            return View();
        }

        [Route("/{user}/{package}/version/upload/{id}")]
        public IActionResult VersionUpload(string user, string package, string id)
        {
            return View();
        }

        [Route("/{user}/{package}/version/delete/{id}")]
        public IActionResult VersionDelete(string user, string package, string id)
        {
            return View();
        }

        [Route("/{user}/{package}/branch/new")]
        public IActionResult BranchNew(string user, string package, string id)
        {
            return View();
        }

        [Route("/{user}/{package}/branch/edit/{name}")]
        public IActionResult BranchEdit(string user, string package, string name)
        {
            return View();
        }

        [Route("/{user}/{package}/branch/delete/{name}")]
        public IActionResult BranchDelete(string user, string package, string name)
        {
            return View();
        }
    }
}
