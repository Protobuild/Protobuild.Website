using System.Collections.Generic;
using Microsoft.AspNetCore.Mvc;
using Protobuild.Website.Models;

namespace Protobuild.Website.Controllers
{
    public class IndexController : Controller
    {
        [Route("/index")]
        public IActionResult Index(string q)
        {
            return View(q);
        }
    }
}
