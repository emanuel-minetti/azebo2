export default class User {
  fullName: string;

  constructor(data?: any) {
    //TODO remove debugging!
    //TODO show user name on header
    console.log("Angekommen: " + data);
    if (data && data.given_name && data.name) {
      console.log("Angekommen2");
      this.fullName = data.given_name + " " + data.name;
    } else {
      console.log("Angekommen3");
      this.fullName = "";
    }
  }
}
