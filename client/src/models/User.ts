export default class User {
  fullName: string;

  constructor(data?: any) {
    if (data && data.given_name && data.name) {
      this.fullName = data.given_name + " " + data.name;
    } else {
      this.fullName = "";
    }
  }
}
